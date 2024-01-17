<?php

namespace Drupal\va_gov_api\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Provides a computed breadcrumbs field item list.
 */
class ComputedSituationUpdatesItemList extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue() {
    $parent = $this->getEntity();
    if ($parent->isNew()) {
      return;
    }

    // Get paragraph ids from 'field_situation_updates' field.
    $situation_updates = $parent->get('field_situation_updates')->getValue();
    $situation_update_ids = array_column($situation_updates, 'target_id');

    // Load paragraph entities.
    $paragraphs = \Drupal::entityTypeManager()
      ->getStorage('paragraph')
      ->loadMultiple($situation_update_ids);

    foreach ($paragraphs as $key => $paragraph) {
      $paragraphData = [
        'revision_id' => $paragraph->getRevisionId(),
        'paragraph_type' => $paragraph->getType(),
        'uuid' => $paragraph->uuid(),
        'field_datetime_range_timezone' => $paragraph->get('field_datetime_range_timezone')
          ->getValue()[0],
        'field_send_email_to_subscribers' => $paragraph->get('field_send_email_to_subscribers')
          ->getValue()[0],
        'field_wysiwyg' => $paragraph->get('field_wysiwyg')->getValue()[0],
      ];

      $this->list[$key] = $this->createItem($key, $paragraphData);
    }
  }

}
