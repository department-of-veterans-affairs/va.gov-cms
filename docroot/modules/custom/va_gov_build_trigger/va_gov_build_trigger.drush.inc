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
  ];

  $items['va-gov-get-web-build-status'] = [
    'description' => 'Get the web build status.',
  ];

  $items['va-gov-set-web-build-status'] = [
    'description' => 'Set the web build status.',
    'arguments' => [
      'status' => 'The intended web build status.',
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
  $build_frontend = \Drupal::service('va_gov_build_trigger.build_frontend');
  $build_commands = \Drupal::service('va_gov.build_trigger.web_build_command_builder')
    ->buildCommands($reference, $full_rebuild);
  if ($dry_run = drush_get_option('dry-run')) {
    echo '# $reference: ' . $reference . PHP_EOL;
    echo '# $full_rebuild: ' . ($full_rebuild ? 'TRUE' : 'FALSE') . PHP_EOL;
    foreach ($build_commands as $build_command) {
      echo $build_command . PHP_EOL;
    }
  }
  else {
    $build_frontend->triggerFrontendBuild($reference, $full_rebuild);
  }
}

/**
 * Drush command to get the current web build status.
 */
function drush_va_gov_build_trigger_va_gov_get_web_build_status() {
  $web_build_status = \Drupal::service('va_gov.build_trigger.web_build_status');
  echo ($web_build_status->getWebBuildStatus() ? 'ENABLED' : 'DISABLED') . PHP_EOL;
}

/**
 * Drush command to set the current web build status.
 *
 * @param string $status
 *   The intended web build status.
 */
function drush_va_gov_build_trigger_va_gov_set_web_build_status(string $status) {
  $status = filter_var($status, FILTER_VALIDATE_BOOLEAN);
  $web_build_status = \Drupal::service('va_gov.build_trigger.web_build_status');
  $web_build_status->setWebBuildStatus($status);
  if ($web_build_status->getWebBuildStatus() !== $status) {
    throw new \Exception('Failed to set Web Build Status.');
  }
  drush_log(t('Web Build Status is now :status.', [
    ':status' => ($status ? 'active' : 'inactive'),
  ]), LogLevel::OK);
}

/**
 * Drush command to get the current display mode.
 */
function drush_va_gov_build_trigger_va_gov_get_deploy_mode() {
  $site_state = \Drupal::getContainer()->get('va_gov.site_status');
  $site_status = $site_state->inDeployMode() ? 'ENABLED' : 'DISABLED';
  drush_log($site_status, LogLevel::OK);
}

/**
 * Drush command to get the current display mode.
 */
function drush_va_gov_build_trigger_va_gov_enable_deploy_mode() {
  $site_state = \Drupal::getContainer()->get('va_gov.site_status');
  $site_state->enableDeployMode();
  drush_log('Deploy mode has been enabled', LogLevel::OK);
}

/**
 * Drush command to get the current display mode.
 */
function drush_va_gov_build_trigger_va_gov_disable_deploy_mode() {
  $site_state = \Drupal::getContainer()->get('va_gov.site_status');
  $site_state->disableDeployMode();
  drush_log('Deploy mode has been disabled', LogLevel::OK);
}
