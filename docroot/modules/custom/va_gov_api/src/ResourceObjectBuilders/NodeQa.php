<?php

namespace Drupal\va_gov_api\ResourceObjectBuilders;

use Drupal\jsonapi\JsonApiResource\LinkCollection;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\node\NodeInterface;

/**
 * Construct and return a ResourceObject for node--q_a entities.
 */
class NodeQa {

  /**
   * Create a ResourceObject from a `q_a` entity.
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
    /** @var \Drupal\paragraphs\Entity\Paragraph $answer_entity */
    $answer_entity = $entity->field_answer->entity;

    $alert_field = $entity->field_alert_single->entity->field_alert_non_reusable_ref->entity->field_va_paragraphs->entity->field_wysiwyg->processed;

    $links = [];
    foreach ($entity->get('field_buttons') as $button_field) {
      /** @var \Drupal\paragraphs\Entity\Paragraph $button_paragraph */
      $button_paragraph = $button_field->entity;

      /** @var \Drupal\link\LinkItemInterface */
      $button_paragraph_link = $button_paragraph->field_button_link->first();
      if (empty($button_paragraph_link)) {
        continue;
      }
      $button_url = $button_paragraph_link->getUrl();
      if (!$button_url->isRouted()) {
        $url_string = $button_url->toString();
      }
      else {
        // @todo fix this direct service call.
        /** @var \Drupal\node\NodeInterface */
        $related_node = \Drupal::service('entity_type.manager')->getStorage('node')->load($button_url->getRouteParameters()['node']);
        // @phpstan-ignore-next-line
        $url_string = $related_node->path->alias;
      }

      $links[] = [
        'button_label' => $button_paragraph->field_button_label->value,
        'button_link' => $url_string,
      ];
    }

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
