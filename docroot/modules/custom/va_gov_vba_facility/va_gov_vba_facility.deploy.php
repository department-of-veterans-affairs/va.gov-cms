<?php

/**
 * @file
 * Install file for VA VBA Facility.
 */

/**
 * Copy the title to the official name field for all VBA Facility nodes.
 */
function va_gov_vba_facility_deploy_vba_copy_title_to_official_name(&$sandbox) {
  $script = \Drupal::classResolver('\Drupal\va_gov_batch\cbo_scripts\VBACopyTitleToOfficialName');
  return $script->run($sandbox, 'deploy');
}
