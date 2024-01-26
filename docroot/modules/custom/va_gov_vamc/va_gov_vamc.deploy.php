<?php

/**
 * @file
 * Post deploy file VA Gov VAMC.
 */

use Drupal\va_gov_vamc\ServiceLocationMigration;

/**
 * Build top VA Police page and menu items for each system.
 */
function va_gov_vamc_deploy_build_va_police_9001(&$sandbox) {
  \Drupal::moduleHandler()->loadInclude('va_gov_vamc', 'install');
  $build_bundle = 'vamc_system_va_police';
  _va_gov_vamc_sandbox_init($sandbox, '_va_gov_vamc_get_systems_to_process', [$build_bundle]);
  _va_gov_vamc_create_system_content_pages($sandbox, $build_bundle, 'draft');
  return _va_gov_vamc_sandbox_complete($sandbox, "Created @total {$build_bundle} nodes.");
}

/**
 * Migrate some facility service data to service location paragraphs.
 */
function va_gov_vamc_deploy_build_va_police_9002(&$sandbox) {
  \Drupal::moduleHandler()->loadInclude('va_gov_vamc', 'install');
  $source_bundle = 'health_care_local_health_service';
  _va_gov_vamc_sandbox_init($sandbox, '_va_gov_vamc_get_nids_of_type', [$source_bundle, FALSE]);
  $migration = new ServiceLocationMigration($sandbox);
  $new_service_locations = $migration->getCreatedServiceLocations();
  $updated_service_locations = $migration->getUpdatedServiceLocations();
  return _va_gov_vamc_sandbox_complete($sandbox, "migrated @total {$source_bundle} nodes into {$new_service_locations} new service_location paragraphs, and {$updated_service_locations} updated .");
}
