<?php

// @codingStandardsIgnoreFile

/**
 * This file, settings.devshop.php, is included automatically in the generated
 * settings.php file on devshop.cms.va.gov.
 * This file path must be sites/default/settings.devshop.php to be included.
 * e.g. Do not move to the sites/default/settings/ directory.
 */


/**
 * DEVSHOP SETTINGS.PHP OVERRIDES
 */

// The file, settings.php, on devshop.cms.va.gov has it's own $databases array.
// We copy it so we can restore it after we import our global settings.php file.
$devshop_db_settings = $databases;

// This gets references in settings.php, so has to be defined here.
$webhost_on_cli = $_SERVER['DRUPAL_ADDRESS'];

// This brings back in our defaults but wipes DevShop's DB settings.
if (file_exists($app_root . '/' . $site_path . '/../default/settings.php')) {
  include $app_root . '/' . $site_path . '/../default/settings.php';
}

$settings['file_public_base_url'] = $_SERVER['DRUPAL_ADDRESS'] . '/' . $site_path . '/files';

// Add devshop level service file for FileSystem overrides
$settings['file_chmod_directory'] = 02775;
$settings['skip_permissions_hardening'] = TRUE;

// Restore DevShop's $databases settings.
$databases  = $devshop_db_settings;

// Set cookie_lifetime to 0 for SSO requirements.
// We set this in services.yml and would prefer that value to take effect anyways.
// DevShop shouldn't set this, maybe we can just unset this so services.yml is
// authoritative source for the cookie_lifetime.
ini_set('session.cookie_lifetime', 0);

/**
 * GLOBAL SETTINGS.PHP OVERRIDES
 *
 * Put regular settings.php overrides below this section.
 */
$config['config_split.config_split.dev']['status'] = TRUE;
$config['config_split.config_split.stg']['status'] = FALSE;
$config['config_split.config_split.prod']['status'] = FALSE;
$config['config_split.config_split.config_dev']['status'] = TRUE;
$config['system.performance']['cache']['page']['use_internal'] = FALSE;
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['css']['gzip'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$config['system.performance']['js']['gzip'] = FALSE;
$config['system.performance']['response']['gzip'] = FALSE;
$config['views.settings']['ui']['show']['sql_query']['enabled'] = TRUE;
$config['views.settings']['ui']['show']['performance_statistics'] = TRUE;
$config['system.logging']['error_level'] = 'all';
$config['environment_indicator.indicator']['bg_color'] = '#79D4F0'; // light blue.
$config['environment_indicator.indicator']['fg_color'] = '#000000';
$config['environment_indicator.indicator']['name'] = 'CI';

$settings['trusted_host_patterns'] = [
  // For ALB/ELB Healthchecks.
  '10\.199.*',
  '10\.247.*',
  'localhost',
  '^.*\.ci\.cms\.va\.gov',
];

// Github token for migrations
$settings['va_cms_bot_github_auth_token'] = getenv('GITHUB_TOKEN') ?: FALSE;
