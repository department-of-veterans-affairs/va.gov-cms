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
function drush_va_gov_build_trigger_va_gov_build_frontend(string $reference = NULL, string $fullRebuild = 'FALSE') {
  $buildFrontend = \Drupal::service('va_gov_build_trigger.build_frontend');
  $buildFrontend->buildFrontend($reference, $fullRebuild);
}

/**
 * Drush command to get the current web build status.
 */
function drush_va_gov_build_trigger_va_gov_get_web_build_status() {
  $webBuildStatus = \Drupal::service('va_gov.build_trigger.web_build_status');
  echo ($webBuildStatus->getWebBuildStatus() ? 'ENABLED' : 'DISABLED') . PHP_EOL;
}

/**
 * Drush command to set the current web build status.
 *
 * @param string $status
 *   The intended web build status.
 */
function drush_va_gov_build_trigger_va_gov_set_web_build_status(string $status) {
  $status = filter_var($status, FILTER_VALIDATE_BOOLEAN);
  $webBuildStatus = \Drupal::service('va_gov.build_trigger.web_build_status');
  $webBuildStatus->setWebBuildStatus($status);
  if ($webBuildStatus->getWebBuildStatus() !== $status) {
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
