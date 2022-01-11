<?php

namespace Drupal\va_gov_api\ResourceObjectBuilders;

use Drupal\jsonapi\JsonApiResource\LinkCollection;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\node\NodeInterface;

/**
 * Construct and return a ResourceObject for node--banner entities.
 */
class NodeBanner {

  /**
   * Create a ResourceObject from a `banner` entity.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The `banner` entity.
   * @param \Drupal\jsonapi\ResourceType\ResourceType $resource_type
   *   The ResourceType for `banner` entities.
   *
   * @return \Drupal\jsonapi\JsonApiResource\ResourceObject
   *   A ResourceObject constructed from a `banner` entity.
   */
  public static function buildResourceObject(NodeInterface $entity, ResourceType $resource_type): ResourceObject {
    /** @var \Drupal\taxonomy\TermInterface $section_term */
    $section_term = $entity->field_administration->entity;
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

    return new ResourceObject(
      $entity,
      $resource_type,
      $entity->uuid(),
      NULL,
      $data,
      new LinkCollection([])
    );
  }

}
