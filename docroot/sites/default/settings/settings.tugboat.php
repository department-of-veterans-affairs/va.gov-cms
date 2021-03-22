<?php

// @codingStandardsIgnoreFile

$databases['default']['default'] = array (
  'database' => 'tugboat',
  'username' => 'tugboat',
  'password' => 'tugboat',
  'prefix' => '',
  'host' => 'mysql',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
// Use the TUGBOAT_REPO_ID to generate a hash salt for Tugboat sites.
$settings['hash_salt'] = hash('sha256', getenv('TUGBOAT_REPO_ID'));

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
$config['environment_indicator.indicator']['bg_color'] = '#533B03'; // dark brown.
$config['environment_indicator.indicator']['fg_color'] = '#ffffff';
$config['environment_indicator.indicator']['name'] = 'Tugboat';

$settings['trusted_host_patterns'] = [
  '^localhost$',
  '^.*' . getenv('TUGBOAT_SERVICE_TOKEN') . '.' . getenv('TUGBOAT_SERVICE_CONFIG_DOMAIN') . '$'];

// Github token for migrations
$settings['va_cms_bot_github_auth_token'] = getenv('GITHUB_TOKEN') ?: FALSE;

// Add devshop level service file for FileSystem overrides
$settings['file_chmod_directory'] = 02775;
$settings['skip_permissions_hardening'] = TRUE;

$webhost_on_cli = getenv('DRUPAL_ADDRESS');

// Link to this file locally since lando can not access prod where the real
// file exists.  You will need to copy the file from the same path on prod.
$config['migrate_plus.migration.va_node_form']['source']['urls'] = [$webhost_on_cli . '/sites/default/files/migrate_source/va_forms_data.csv'];
$settings['va_gov_frontend_url'] = getenv('FRONTEND_ADDRESS');
$settings['va_gov_frontend_build_type'] = 'tugboat';
$settings['va_gov_app_root'] = getenv('TUGBOAT_ROOT');
$settings['va_gov_web_root'] = getenv('TUGBOAT_ROOT') . '/web';

// Memcached setup
//// Memcache
//$settings['cache']['default'] = 'cache.backend.memcache';
//$settings['memcache']['servers'] = ['memcache:11211' => 'default'];
