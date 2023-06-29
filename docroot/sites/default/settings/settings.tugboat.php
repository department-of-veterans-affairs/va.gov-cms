<?php

// phpcs:ignoreFile

$settings['va_gov_environment']['environment'] = 'tugboat';

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

$config['config_split.config_split.dev']['status'] = FALSE;
$config['config_split.config_split.stg']['status'] = FALSE;
$config['config_split.config_split.prod']['status'] = FALSE;
$config['config_split.config_split.local']['status'] = FALSE;
$config['config_split.config_split.tugboat']['status'] = TRUE;

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
$config['environment_indicator.indicator']['bg_color'] = '#02BFE7';
$config['environment_indicator.indicator']['fg_color'] = '#212121';
$config['environment_indicator.indicator']['name'] = 'Tugboat';

$settings['trusted_host_patterns'] = [
  '^localhost$',
  '^.*' . getenv('TUGBOAT_SERVICE_TOKEN') . '.' . getenv('TUGBOAT_SERVICE_CONFIG_DOMAIN') . '$',
];

// Github token for migrations
$settings['va_cms_bot_github_auth_token'] = getenv('GITHUB_TOKEN') ?: FALSE;

// Add Tugboat level service file for FileSystem overrides.
$settings['file_chmod_directory'] = 02775;
$settings['skip_permissions_hardening'] = TRUE;

$webhost_on_cli = getenv('DRUPAL_ADDRESS');
$settings['webhost'] = $webhost_on_cli;

// Link to this file locally since Tugboat can not access prod where the real
// file exists.  You will need to copy the file from the same path on prod.
$config['migrate_plus.migration.va_node_form']['source']['urls'] = [$webhost_on_cli . '/sites/default/files/migrate_source/va_forms_data.csv'];
$settings['va_gov_frontend_url'] = getenv('FRONTEND_ADDRESS');
$settings['va_gov_frontend_build_type'] = 'tugboat';
$settings['va_gov_app_root'] = getenv('TUGBOAT_ROOT');
$settings['va_gov_web_root'] = getenv('TUGBOAT_ROOT') . '/web';

$settings['memcache']['servers'] = [
  'memcache:11211' => 'default',
];

$settings['cms_datadog_api_key'] = getenv('CMS_DATADOG_API_KEY');

// Uncomment this line to temporarily enable sending metrics to datadog on cron.
//$settings['va_gov_force_sending_metrics'] = true;

// PIV login does not currently work on Tugboat.
//
// To avoid confusing editors, we want to disable PIV login completely on 
// Tugboat demo environments.
//
// However, we want to _preserve_ the PIV login interface on Tugboat PR
// environments so that we can test the login page and the logic behind it
// as it would run on staging and production.
//
// Therefore, we should enable the PIV login interface on PR environments
// and disable it everywhere else.
$tugboat_preview_type = getenv('TUGBOAT_PREVIEW_TYPE');
$is_pull_request = $tugboat_preview_type === 'pullrequest';
$config['simplesamlphp_auth.settings']['activate'] = $is_pull_request;

// Settings supporting broken link report import.
$settings['broken_link_report_import_enabled'] = TRUE;
$settings['broken_link_report_location'] = '/var/lib/tugboat/docroot/vendor/va-gov/content-build/logs/vagovdev-broken-links.json';
