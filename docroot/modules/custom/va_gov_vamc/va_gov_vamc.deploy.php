<?php

/**
 * @file
 * Deploy hooks for va_gov_vamc.
 *
 * This is a NAME.deploy.php file. It contains "deploy" functions. These are
 * one-time functions that run *after* config is imported during a deployment.
 * These are a higher level alternative to hook_update_n and
 * hook_post_update_NAME functions.
 *
 * See https://www.drush.org/latest/deploycommand/#authoring-update-functions
 * for a detailed comparison.
 */

declare(strict_types=1);

use Drupal\va_gov_batch\cbo_scripts\MigrateVamcFacilityMentalHealthPhoneFieldToParagraph;

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
 * Runs data migration for VAMC Facility Mental health phone field to paragraph.
 */
function va_gov_vamc_deploy_move_phone_to_paragraph_10001(array &$sandbox): void {
  \Drupal::classResolver(MigrateVamcFacilityMentalHealthPhoneFieldToParagraph::class)->run($sandbox, 'deploy');
}
