<?php

/**
 * @file
 * Migrate data from VAMC Facility Service nodes to Service Location paragraphs.
 *
 *  @see https://github.com/department-of-veterans-affairs/va.gov-cms/issues/15559
 *
 * This updates ~15,800 Facility services. It takes roughly 2 hours to run.
 * It should only be run 1 time or it will duplicate phone data.
 *
 * If for some reason the run crashes before it is complete:
 * - It is safe to re-run the script as it should pick up where it left off.
 * - Go to /admin/config/va-gov-post-api/config
 * - Uncheck the box for 'Bypass data comparison'
 * - Click the 'Save' button.
 */

use Drupal\va_gov_vamc\ServiceLocationMigration;

require_once __DIR__ . '/script-library.php';

run();

/**
 * Executes the intended functionality. Runs by default when the script is run.
 *
 * @return string
 *   Indicating the run is complete.
 */
function run(): string {
  script_library_skip_post_api_data_check(TRUE);
  $sandbox = ['#finished' => 0];
  do {
    print(va_gov_vamc_deploy_migrate_service_data_to_service_location($sandbox));
  } while ($sandbox['#finished'] < 1);
  // Migration is done.
  script_library_skip_post_api_data_check(FALSE);
  return "Script run complete.";
}

/**
 * Migrate some facility service data to service location paragraphs.
 */
function va_gov_vamc_deploy_migrate_service_data_to_service_location(&$sandbox) {
  $source_bundle = 'health_care_local_health_service';
  script_library_sandbox_init($sandbox, 'get_nids_of_type', [$source_bundle, FALSE]);
  $migrator = new ServiceLocationMigration();
  $msg = $migrator->run($sandbox);
  $new_service_locations = $sandbox['service_locations_created_count'] ?? 0;
  $updated_service_locations = $sandbox['service_locations_updated_count'] ?? 0;
  $forward_revisions = $sandbox['forward_revisions_count'] ?? 0;
  return $msg . script_library_sandbox_complete($sandbox, "migrated @total {$source_bundle} nodes into {$new_service_locations} new service_location paragraphs, and {$updated_service_locations} updated. Also updated {$forward_revisions} forward revisions.");
}
