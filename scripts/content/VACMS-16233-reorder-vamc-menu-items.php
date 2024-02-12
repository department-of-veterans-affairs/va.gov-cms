<?php

/**
 * @file
 * Re-order all VAMC system menus to match pattern.
 *
 *  @see https://github.com/department-of-veterans-affairs/va.gov-cms/issues/16233
 *
 * This updates 139 VAMC System menus. It takes roughly 38 minutes to run.
 * Running multiple times causes no issue.
 *
 * If for some reason the run crashes before it is complete:
 * - Check CMS recent log messages for cause
 * /admin/reports/dblog?type%5B%5D=codit_menu_tools
 * - Simply re-run the script.
 */

use Drupal\codit_menu_tools\MenuManipulator;

require_once __DIR__ . '/script-library.php';

run();

/**
 * Executes the intended functionality. Runs by default when the script is run.
 *
 * @return string
 *   Indicating the run is complete.
 */
function run(): string {
  $sandbox = ['#finished' => 0];
  do {
    print(va_gov_vamc_deploy_resort_vamc_menus($sandbox));
  } while ($sandbox['#finished'] < 1);

  return "Script run complete. All menus should have been updated. ";
}

/**
 * Re-sort VAMC menus.
 *
 * @param mixed $sandbox
 *   Batch sandbox to keep state during multiple runs.
 *
 * @return string
 *   The message to be output.
 */
function va_gov_vamc_deploy_resort_vamc_menus(&$sandbox) {
  script_library_sandbox_init($sandbox, '_va_gov_vamc_get_system_menus', []);
  _va_gov_vamc_arrange_menus($sandbox);
  return script_library_sandbox_complete($sandbox, "Re-arranged @total VAMC System Menus.");
}

/**
 * Get all VAMC system menus.
 *
 * @return array
 *   An array of VAMC system menus ['machine_name' => 'Human Name'].
 */
function _va_gov_vamc_get_system_menus(): array {
  // Load the menus.
  $vamc_menus = MenuManipulator::getAllMenuNames('-health-');
  $non_name_compliant_menus = [
    'va-central-western-massachusetts' => 'VA Central Western Massachusetts health care',
    'va-columbia-south-carolina-healt' => 'VA Columbia South Carolina health care',
    'va-lebanon' => 'VA Lebanon health care',
  ];

  return array_merge($vamc_menus, $non_name_compliant_menus);
}

/**
 * Re-arranges existing VAMC menus to match a pattern.
 *
 * @param mixed $sandbox
 *   Batch sandbox to keep state during multiple runs.
 *
 * @return string
 *   A message to be output.
 */
function _va_gov_vamc_arrange_menus(&$sandbox) {
  $menu_name = array_key_first($sandbox['items_to_process']);
  $pattern = [
    'About us',
    'Programs',
    'Research',
    'Policies',
    'VA police',
    'Work with us',
    'Contact us',
  ];
  $menu_arranger = new MenuManipulator($menu_name);
  $menu_arranger->matchPattern($pattern);
  $message = "The menu {$sandbox['items_to_process'][$menu_name]} had been rearranged.";
  unset($sandbox['items_to_process'][$menu_name]);
  $sandbox['current']++;

  return $message;
}
