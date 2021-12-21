<?php

namespace Drupal\va_gov_api\Resource;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Path\PathMatcher;
use Drupal\jsonapi\JsonApiResource\LinkCollection;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\jsonapi\JsonApiResource\ResourceObjectData;
use Drupal\jsonapi_resources\Resource\EntityResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\jsonapi\ResourceResponse;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Get a response for Banner resource.
 */
class BannerAlerts extends EntityResourceBase implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * PatchMatcher utility.
   *
   * @var \Drupal\Core\Path\PathMatcher
   */
  protected $pathMatcher;

  /**
   * Constructs a new EntityResourceBase object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Tne entity type manager.
   * @param \Drupal\Core\Path\PathMatcher $path_matcher
   *   Drupal's internal path matching service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PathMatcher $path_matcher) {
    $this->entityTypeManager = $entity_type_manager;
    $this->pathMatcher = $path_matcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('path.matcher'),
    );
  }

  /**
   * Process the resource request.
   *
   * THIS IS POC CODE AND IS NOT PRODUCTION READY.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param \Drupal\jsonapi\ResourceType\ResourceType[] $resource_types
   *   The route resource types.
   *
   * @return \Drupal\jsonapi\ResourceResponse
   *   The response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function process(Request $request, array $resource_types) : ResourceResponse {
    // Given a path, return any banner nodes that should display on it.
    $path = $request->get('item-path');

    // We need to load the banners in order to test the path.
    $node_storage = $this->entityTypeManager->getStorage('node');
    $query = $node_storage->getQuery();
    $query->condition('type', 'banner')->condition('status', TRUE);
    $banner_nids = $query->execute();
    /** @var \Drupal\node\NodeInterface[] $banners */
    $banners = [];
    /** @var \Drupal\node\NodeInterface[] $matchedBanners */
    $matchedBanners = [];
    $primary_data = [];
    if (count($banner_nids)) {
      $banners = $node_storage->loadMultiple(array_values($banner_nids));
    }
    /** @var \Drupal\node\NodeInterface[] $banners */
    foreach ($banners as $banner) {
      $patterns = '';
      $pathChecks = $banner->field_target_paths->getValue();
      // Convert values to a string that PathMatcher expects.
      foreach ($pathChecks as $pathCheck) {
        $patterns = $patterns . $pathCheck['value'] . "\n";
      }
      if ($this->pathMatcher->matchPath($path, $patterns)) {
        $matchedBanners[] = $banner;
      }
    }
    $resource_type = reset($resource_types);
    // $cache_metadata = [];
    $datum = NULL;
    foreach ($matchedBanners as $entity) {
      // Following line commented; need to figure out cache_metadata for > 1.
      // $cache_metadata[] = CacheableMetadata::createFromObject($entity);
      // $datum = CacheableMetadata::createFromObject($entity);
      // Currently assumes Banner ctype.
      /** @var \Drupal\taxonomy\TermInterface $section_term */
      $section_term = $entity->field_administration->entity;
      // We need to transform multivalue field output because Drupal.
      $data = [
        'nid' => $entity->id(),
        'uuid' => $entity->uuid(),
        'langcode' => $entity->language()->getId(),
        'status' => $entity->isPublished(),
        'bundle' => $entity->getType(),
        'heading' => $entity->label(),
        'moderation_state' => $entity->get('moderation_state')->getString(),
        'alert_type' => $entity->field_alert_type->value,
        // @phpstan-ignore-next-line
        'text' => $entity->body->processed,
        'dismissible' => $entity->field_dismissible_option->value,
        'section_name' => $section_term->label(),
        'paths' => array_column($entity->field_target_paths->getValue(), 'value'),
      ];

      $primary_data[] = new ResourceObject(
        $entity,
        $resource_type,
        $entity->uuid(),
        NULL,
        $data,
        new LinkCollection([])
      );
    }

    $top_level_data = new ResourceObjectData($primary_data);
    /** @var \Drupal\jsonapi\ResourceResponse $response */
    $response = $this->createJsonapiResponse($top_level_data, $request);

    // Unsure of how we're meant to handle this, but: each returned item should
    // be able to invalidate cache.
    // Question: How do we invalidate cache in the case where a new banner is
    // published?
    /* foreach ($cache_metadata as $datum) { */
    // @phpstan-ignore-next-line
    $response->addCacheableDependency($datum);
    /* } */

    return $response;
  }

}
