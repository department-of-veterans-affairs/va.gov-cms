<?php

/**
 * @file
 * Provide views data for content_moderation.module.
 *
 * @ingroup views_module_handlers
 */

use Drupal\expirable_content\ViewsData;

/**
 * Implements hook_views_data().
 */
function expirable_content_views_data(): array {
  $data = new ViewsData(
    \Drupal::service('entity_type.manager'),
    \Drupal::service('expirable_content.information')
  );
  return $data->getViewsData();
}

