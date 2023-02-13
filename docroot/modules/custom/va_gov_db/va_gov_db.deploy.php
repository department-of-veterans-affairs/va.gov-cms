<?php

/**
 * @file
 * Deploy hooks for va_gov_db.
 *
 * This is a NAME.deploy.php file. It contains "deploy" functions. These are
 * one-time functions that run *after* config is imported during a deployment.
 * These are a higher level alternative to hook_update_n and
 * hook_post_update_NAME functions.
 *
 * See https://www.drush.org/latest/deploycommand/#authoring-update-functions
 * for a detailed comparison.
 */

require_once __DIR__ . '/../../../../scripts/content/script-library.php';

/**
 * Populate new destination terms.
 */
function va_gov_db_deploy_new_destination_terms_a(array &$sandbox) {
  switch_user();
  $terms = [
    // Name => description.
    'Facility API' => 'The Facility API is an aggregator of facility data that comes from many sources. (VAST, Access to care, VBA Database...)',
    'VA Forms API' => 'The VA forms API is an aggregator of Form data from the CMS.',
    'VA Forms Database' => 'The VA Forms Database is the upstream source from form managers related to Forms',
  ];
  $vocabulary = 'external_data_source_destination';
  $count = save_new_terms($vocabulary, $terms);
  return "Created {$count} terms in {$vocabulary} vocabulary.";
}
