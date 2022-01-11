<?php

namespace Drupal\va_gov_api\ResourceObjectBuilders;

use Drupal\jsonapi\JsonApiResource\LinkCollection;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\node\NodeInterface;

/**
 * Build and return a ResourceObject for node--full_width_banner_alert entities.
 */
class NodeFullWidthBannerAlert {

  /**
   * Create a ResourceObject from a `full_width_banner_alert` entity.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The `full_width_banner_alert` entity.
   * @param \Drupal\jsonapi\ResourceType\ResourceType $resource_type
   *   The ResourceType for `full_width_banner_alert` entities.
   *
   * @return \Drupal\jsonapi\JsonApiResource\ResourceObject
   *   A ResourceObject constructed from a `full_width_banner_alert` entity.
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
      // @phpstan-ignore-next-line
      'text' => $entity->field_body->processed,
      'alert_type' => $entity->field_alert_type->value,
      'dismissable' => $entity->field_alert_dismissable->value,
      'section_name' => $section_term->label(),
      // Paths is not relevant to the facility banner, but it's in spec.
      'paths' => '',
      // @phpstan-ignore-next-line
      'situational_info' => $entity->field_banner_alert_situationinfo->processed,
      'show_find_facilities_cta' => $entity->field_alert_find_facilities_cta->value,
      'show_operating_status_cta' => $entity->field_alert_operating_status_cta->value,
      'show_email_update_button' => $entity->field_alert_email_updates_button->value,
      'limit_subpages' => $entity->field_alert_inheritance_subpages->value,
      'send_email_updates' => $entity->field_operating_status_sendemail->value,
      'situation_updates' => 'String for POC; this should point at a schema rather than being a string.',
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
