<?php

/**
 * @file
 * Deploy hooks for va_gov_profile.
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

use Drupal\va_gov_batch\cbo_scripts\MigratePhoneFieldToParagraph;

/**
 * Implements hook_deploy_NAME().
 */
function va_gov_profile_deploy_move_phone_to_paragraph(array &$sandbox): void {
  \Drupal::classResolver(MigratePhoneFieldToParagraph::class)->run($sandbox, 'deploy');
}
