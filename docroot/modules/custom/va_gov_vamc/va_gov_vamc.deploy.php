<?php

/**
 * @file
 * Post deploy file VA Gov VAMC.
 */

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
