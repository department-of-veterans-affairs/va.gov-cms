<?php

/**
 * @file
 * Post deploy file VA Gov VAMC.
 */

use Drupal\va_gov_vamc\ServiceLocationMigration;

require_once __DIR__ . '/../../../../scripts/content/script-library.php';

/**
 * Build top VA Police page and menu items for each system.
 */
function va_gov_vamc_deploy_build_va_police_9001(&$sandbox) {
  \Drupal::moduleHandler()->loadInclude('va_gov_vamc', 'install');
  $build_bundle = 'vamc_system_va_police';
  script_library_sandbox_init($sandbox, '_va_gov_vamc_get_systems_to_process', [$build_bundle]);
  _va_gov_vamc_create_system_content_pages($sandbox, $build_bundle, 'draft');
  return script_library_sandbox_complete($sandbox, "Created @total {$build_bundle} nodes.");
}

/**
 * Migrate some facility service data to service location paragraphs.
 */
function va_gov_vamc_deploy_migrate_service_data_to_service_location_9003(&$sandbox) {

  $source_bundle = 'health_care_local_health_service';
  script_library_sandbox_init($sandbox, 'get_nids_of_type', [$source_bundle, FALSE]);
  script_library_toggle_post_api_queueing(TRUE);
  new ServiceLocationMigration($sandbox);
  $new_service_locations = $sandbox['service_locations_created_count'] ?? 0;
  $updated_service_locations = $sandbox['service_locations_updated_count'] ?? 0;
  $forward_revisions = $sandbox['forward_revisions_count'] ?? 0;
  script_library_toggle_post_api_queueing(FALSE);

  return script_library_sandbox_complete($sandbox, "migrated @total {$source_bundle} nodes into {$new_service_locations} new service_location paragraphs, and {$updated_service_locations} updated. Also updated {$forward_revisions} forward revisions.");
}
