<?php

/**
 * @file
 * CMS Export commands.
 */

use Drush\Log\LogLevel;

/**
 * Implements hook_drush_command().
 */
function va_gov_build_trigger_drush_command() {
  $items = [];

  $items['va-gov-get-deploy-mode'] = [
    'description' => 'Get the deploy mode.',
  ];

  $items['va-gov-set-deploy-mode'] = [
    'description' => 'Set the deploy mode.',
    'arguments' => [
      'mode' => 'The intended deploy mode.',
    ],
  ];

  $items['va-gov-enable-deploy-mode'] = [
    'description' => 'Enable the deploy mode.',
  ];

  $items['va-gov-disable-deploy-mode'] = [
    'description' => 'Disable the deploy mode.',
  ];

  $items['va-gov-build-frontend'] = [
    'description' => 'Build the frontend.',
    'arguments' => [
      'reference' => 'The git reference.',
      'full_rebuild' => 'Whether or not to perform a full rebuild.',
    ],
    'options' => [
      'dry-run' => 'Don\'t actually build; just print the commands that would be executed.',
    ],
    'aliases' => [
      'va-gov-build-web',
    ],
  ];

  $items['va-gov-get-frontend-build-status'] = [
    'description' => 'Get the frontend build status.',
    'aliases' => [
      'va-gov-get-web-build-status',
    ],
  ];

  $items['va-gov-set-frontend-build-status'] = [
    'description' => 'Set the frontend build status.',
    'arguments' => [
      'status' => 'The intended frontend build status.',
    ],
    'aliases' => [
      'va-gov-set-web-build-status',
    ],
  ];

  return $items;
}

/**
 * Drush command to build the frontend.
 */
function drush_va_gov_build_trigger_va_gov_build_frontend(string $reference = NULL, string $full_rebuild = 'FALSE') {
  if (filter_var($reference, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== NULL) {
    $full_rebuild = $reference;
    $reference = NULL;
  }
  if (empty($reference)) {
    $reference = NULL;
  }
  $full_rebuild = filter_var($full_rebuild, FILTER_VALIDATE_BOOLEAN);
  $build_commands = \Drupal::service('va_gov_build_trigger.frontend_build.command.builder')
    ->buildCommands($reference, $full_rebuild);
  if ($dry_run = drush_get_option('dry-run')) {
    echo '# $reference: ' . $reference . PHP_EOL;
    echo '# $full_rebuild: ' . ($full_rebuild ? 'TRUE' : 'FALSE') . PHP_EOL;
    foreach ($build_commands as $build_command) {
      echo $build_command . PHP_EOL;
    }
  }
  else {
    $dispatcher = \Drupal::service('va_gov_build_trigger.frontend_build.dispatcher');
    $dispatcher->triggerFrontendBuild($reference, $full_rebuild);
  }
}

/**
 * Drush command to get the current web build status.
 */
function drush_va_gov_build_trigger_va_gov_get_frontend_build_status() {
  $status_service = \Drupal::service('va_gov_build_trigger.frontend_build.status');
  echo ($status_service->getStatus() ? 'TRUE' : 'FALSE') . PHP_EOL;
}

/**
 * Drush command to set the current web build status.
 *
 * @param string $status
 *   The intended web build status.
 */
function drush_va_gov_build_trigger_va_gov_set_frontend_build_status(string $status) {
  $status = filter_var($status, FILTER_VALIDATE_BOOLEAN);
  $status_service = \Drupal::service('va_gov_build_trigger.frontend_build.status');
  $status_service->setStatus($status);
  if ($status_service->getStatus() !== $status) {
    throw new \Exception('Failed to set Frontend Build Status.');
  }
  drush_log(t('Frontend Build Status is now :status.', [
    ':status' => ($status ? 'active' : 'inactive'),
  ]), LogLevel::OK);
}

/**
 * Drush command to get the current display mode.
 */
function drush_va_gov_build_trigger_va_gov_get_deploy_mode() {
  $status_service = \Drupal::service('va_gov_build_trigger.site_status');
  echo ($status_service->getDeployMode() ? 'TRUE' : 'FALSE') . PHP_EOL;
}

/**
 * Drush command to set the current deploy mode.
 *
 * @param string $mode
 *   The intended deploy mode.
 */
function drush_va_gov_build_trigger_va_gov_set_deploy_mode(string $mode) {
  $mode = filter_var($mode, FILTER_VALIDATE_BOOLEAN);
  $status_service = \Drupal::service('va_gov_build_trigger.site_status');
  $status_service->setDeployMode($mode);
  if ($status_service->getDeployMode() !== $mode) {
    throw new \Exception('Failed to set Deploy Mode.');
  }
  drush_log(t('Deploy Mode is now :mode.', [
    ':mode' => ($mode ? 'enabled' : 'disabled'),
  ]), LogLevel::OK);
}

/**
 * Drush command to get the current display mode.
 */
function drush_va_gov_build_trigger_va_gov_enable_deploy_mode() {
  drush_va_gov_build_trigger_va_gov_set_deploy_mode('TRUE');
}

/**
 * Drush command to get the current display mode.
 */
function drush_va_gov_build_trigger_va_gov_disable_deploy_mode() {
  drush_va_gov_build_trigger_va_gov_set_deploy_mode('FALSE');
}
