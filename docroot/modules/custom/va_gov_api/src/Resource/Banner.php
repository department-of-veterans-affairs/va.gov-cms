<?php

namespace Drupal\va_gov_api\Resource;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
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
class Banner extends EntityResourceBase implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new EntityResourceBase object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Tne entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
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
    $banners = [];
    $matchedBanners = [];
    $primary_data = [];
    if (count($banner_nids)) {
      $banners = $node_storage->loadMultiple(array_values($banner_nids));
    }
    foreach ($banners as $idx => $banner) {
      $pathChecks = $banner->field_target_paths->getValue();
      foreach ($pathChecks as $pathCheck) {
        $pathCheck = $pathCheck['value'];
        // @todo Fix following line.
        if (preg_match($pathCheck, $path)) {
          $matchedBanners[] = $banner;
        }
      }
    }

    foreach ($matchedBanners as $entity) {
      $cache_metadata = CacheableMetadata::createFromObject($entity);

      $data = [
        'title' => $entity->getTitle(),
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

    $top_level_data = new ResourceObjectData($primary_data, 1);
    /** @var \Drupal\jsonapi\ResourceResponse $response */
    $response = $this->createJsonapiResponse($top_level_data, $request);

    $response->addCacheableDependency($cache_metadata);

    return $response;
  }

}
