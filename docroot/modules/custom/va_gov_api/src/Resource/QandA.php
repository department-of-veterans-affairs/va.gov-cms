<?php

namespace Drupal\va_gov_api\Resource;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\jsonapi\JsonApiResource\LinkCollection;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\jsonapi\JsonApiResource\ResourceObjectData;
use Drupal\jsonapi_resources\Resource\EntityResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\jsonapi\ResourceResponse;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Get a response for Q and A resource.
 */
class QandA extends EntityResourceBase implements ContainerInjectionInterface {

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
    $path = $request->get('item-path');

    $route = Url::fromUserInput($path);
    if (!$route->isRouted()) {
      // This is an error so error out.
    }

    if ($route->access()) {
      // Access error.
    }

    $params = $route->getRouteParameters();
    // Assumes a `entity key` -> 'entity id' value.
    $entity_type = key($params);
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $this->entityTypeManager->getStorage($entity_type)->load($params[$entity_type]);
    $cache_metadata = CacheableMetadata::createFromObject($entity);

    /** @var \Drupal\taxonomy\TermInterface $section_term */
    $section_term = $entity->field_administration->entity;
    /** @var \Drupal\paragraphs\Entity\Paragraph $answer_entity */
    $answer_entity = $entity->field_answer->entity;

    $alert_field = $entity->field_alert_single->entity->field_alert_non_reusable_ref->entity->field_va_paragraphs->entity->field_wysiwyg->processed;

    $links = [];
    foreach ($entity->get('field_buttons') as $button_field) {
      /** @var \Drupal\paragraphs\Entity\Paragraph $button_paragraph */
      $button_paragraph = $button_field->entity;

      /** @var \Drupal\link\LinkItemInterface */
      $button_paragraph_link = $button_paragraph->field_button_link->first();
      $button_url = $button_paragraph_link->getUrl();
      if (!$button_url->isRouted()) {
        $url_string = $button_url->toString();
      }
      else {
        /** @var \Drupal\node\NodeInterface */
        $related_node = $this->entityTypeManager->getStorage('node')->load($button_url->getRouteParameters()['node']);
        // @phpstan-ignore-next-line
        $url_string = $related_node->path->alias;
      }

      $links[] = [
        'button_label' => $button_paragraph->field_button_label->value,
        'button_link' => $url_string,
      ];
    }

    $resource_type = reset($resource_types);

    $data = [
      'nid' => $entity->id(),
      'uuid' => $entity->uuid(),
      'langcode' => $entity->language()->getId(),
      'status' => $entity->isPublished(),
      'title' => $entity->label(),
      'moderation_state' => $entity->get('moderation_state')->getString(),
      'path' => $entity->get('path'),
      'section_name' => $section_term->label(),
      // @phpstan-ignore-next-line
      'answer_text' => $answer_entity->field_wysiwyg->processed,
      'alert_text' => $alert_field,
      'links' => $links,
    ];

    $primary_data = new ResourceObject(
      $entity,
      $resource_type,
      $entity->uuid(),
      NULL,
      $data,
      new LinkCollection([])
    );

    $top_level_data = new ResourceObjectData([$primary_data], 1);
    /** @var \Drupal\Core\Cache\CacheableResponseInterface */
    $response = $this->createJsonapiResponse($top_level_data, $request);
    $response->addCacheableDependency($cache_metadata);

    return $response;
  }

}
