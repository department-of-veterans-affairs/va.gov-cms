<?php

/**
 * @file
 * CMS Export commands.
 */

use Drupal\va_gov_content_export\ExportCommand\ExportCommandException;
use Drush\Log\LogLevel;

/**
 * Implements hook_drush_command().
 */
function va_gov_content_export_drush_command() {
  $items = [];

  $items['va-gov-cms-export-content'] = [
    'description' => 'Export Content using CMS Export',
    'arguments' => [
      'chunk' => 'A comma separated list of ID pairs in the format entity_type_id:id.',
    ],
    'required-arguments' => TRUE,
  ];

  $items['va-gov-cms-export-all-content'] = [
    'description' => 'Export all content using CMS Export',
    'options' => [
      'process-count' => 'Limits the number of processes to run concurrently.',
      'entity-count' => 'The number of entities to export per process.',
      'delete-existing' => 'Delete the exiting CMS Export folder.',
    ],
  ];

  $items['va-gov-get-deploy-mode'] = [
    'description' => 'Get the deploy mode.',
  ];

  $items['va-gov-enable-deploy-mode'] = [
    'description' => 'Enable the deploy mode.',
  ];

  $items['va-gov-disable-deploy-mode'] = [
    'description' => 'Disable the deploy mode.',
  ];

  $items['va-gov-cms-export-generate-tar'] = [
    'description' => 'Generate a fresh cms export tar file',
  ];

  return $items;
}

/**
 * Drush command for exporting content.
 *
 * @param string $chunk
 *   An string if id pairs as "entity_type:entity_id" separated by ','.
 */
function drush_va_gov_content_export_va_gov_cms_export_content($chunk) {
  $id_pairs = explode(',', $chunk);
  try {
    \Drupal::service('va_gov.content_export.export_command')->exportPairs($id_pairs);
  }
  catch (ExportCommandException $e) {
    drush_log($e->getMessage(), LogLevel::ERROR);
  }

  drush_log('Export Content Command Complete', LogLevel::OK);
}

/**
 * Drush command to export all entities.
 */
function drush_va_gov_content_export_va_gov_cms_export_all_content() {
  $process_count = drush_get_option('process-count', 1);
  $entity_count = drush_get_option('entity-count', 'all');
  $delete_existing = drush_get_option('delete-existing', FALSE);

  $command = \Drupal::service('va_gov.content_export.export_all_command');
  if ($delete_existing) {
    $command->deleteExportDirectories();
  }

  $commands = $command->buildCommands($entity_count);
  $errors = $command->run($commands, $process_count);

  foreach ($errors as $error) {
    drush_log($error, LogLevel::ERROR);
  }

  if (!$errors) {
    drush_log('Exported config, content, and files.', LogLevel::SUCCESS);
  }
}

/**
 * Drush command to get the current display mode.
 */
function drush_va_gov_content_export_va_gov_get_deploy_mode() {
  $site_state = \Drupal::getContainer()->get('va_gov.site_status');
  $site_status = $site_state->inDeployMode() ? 'ENABLED' : 'DISABLED';
  drush_log($site_status, LogLevel::OK);
}

/**
 * Drush command to get the current display mode.
 */
function drush_va_gov_content_export_va_gov_enable_deploy_mode() {
  $site_state = \Drupal::getContainer()->get('va_gov.site_status');
  $site_state->enableDeployMode();
  drush_log('Deploy mode has been enabled', LogLevel::OK);
}

/**
 * Drush command to get the current display mode.
 */
function drush_va_gov_content_export_va_gov_disable_deploy_mode() {
  $site_state = \Drupal::getContainer()->get('va_gov.site_status');
  $site_state->disableDeployMode();
  drush_log('Deploy mode has been disabled', LogLevel::OK);
}

/**
 * Drush command to create a new cms export tar file.
 */
function drush_va_gov_content_export_va_gov_cms_export_generate_tar() {
  $archiveArgs = \Drupal::getContainer()->get('va_gov.content_export.archive_args_factory')->createContentArgs();
  \Drupal::getContainer()->get('va_gov.content_export.archive_directory')->archive($archiveArgs);
  drush_log('Export Tar regenerated', LogLevel::OK);
}
