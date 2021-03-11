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

  return $items;
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
