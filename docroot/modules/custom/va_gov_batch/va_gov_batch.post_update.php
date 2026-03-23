<?php

/**
 * @file
 * Post-update hooks for VA.gov Batch Operations.
 */

/**
 * Backfill empty field_va_form_page_title on existing VA Form nodes.
 *
 * VACMS-23674: Migration does not overwrite this field, so pre-existing nodes
 * need a one-time backfill aligned with va_node_form's title_prefix +
 * displayName (using field_va_form_number).
 */
function va_gov_batch_post_update_backfill_va_form_page_title(&$sandbox) {
  $script = \Drupal::classResolver('\Drupal\va_gov_batch\cbo_scripts\VaFormBackfillPageTitle');
  return $script->run($sandbox, 'post_update');
}
