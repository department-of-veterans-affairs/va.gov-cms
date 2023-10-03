<?php

// phpcs:ignoreFile

/**
 * For documentation and more options
 * @see /docroot/sites/default/default.settings.php
 */

/**
 * @file
 * Drupal 8 configuration file.
 */

$databases['default']['default'] = array(
  'driver' => 'mysql',
  'database' => getenv('CMS_MARIADB_DATABASE') ?: 'drupal8',
  'username' => getenv('CMS_MARIADB_USERNAME') ?: 'drupal8',
  'password' => getenv('CMS_MARIADB_PASSWORD') ?: 'drupal8',
  'prefix' => '',
  // 'db' is the default DB container hostname for local.
  'host' => getenv('CMS_MARIADB_HOST') ?: 'db',
  'port' => 3306,
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
);

/**
 * Salt for one-time login links, cancel links, form tokens, etc.
 *
 * This variable will be set to a random value by the installer. All one-time
 * login links will be invalidated if the value is changed. Note that if your
 * site is deployed on a cluster of web servers, you must ensure that this
 * variable has the same value on each server.
 *
 * For enhanced security, you may set this variable to the contents of a file
 * outside your document root; you should also ensure that this file is not
 * stored with backups of your database.
 *
 * Example:
 * @code
 *   $settings['hash_salt'] = file_get_contents('/home/example/salt.txt');
 * @endcode
 *
 * @todo Change hash_salt and use example above.
 */
$settings['hash_salt'] = 'HJwuroKYGPRzhRHXnWR7H38RkH9rOkJ0WP8C5qD_pRStai8zvFX655aKHZO1gXNXPXhkFRNjoQ';

/**
 * Access control for update.php script.
 *
 * If you are updating your Drupal installation using the update.php script but
 * are not logged in using either an account with the "Administer software
 * updates" permission or the site maintenance account (the account that was
 * created during installation), you will need to modify the access check
 * statement below. Change the FALSE to a TRUE to disable the access check.
 * After finishing the upgrade, be sure to open this file again and change the
 * TRUE back to a FALSE!
 */
$settings['update_free_access'] = FALSE;

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/../default/services.yml';

/**
 * The default list of directories that will be ignored by Drupal's file API.
 *
 * By default ignore node_modules and bower_components folders to avoid issues
 * with common frontend tools and recursive scanning of directories looking for
 * extensions.
 *
 * @see file_scan_directory()
 * @see \Drupal\Core\Extension\ExtensionDiscovery::scanDirectory()
 */
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

/**
 * The default number of entities to update in a batch process.
 *
 * This is used by update and post-update functions that need to go through and
 * change all the entities on a site, so it is useful to increase this number
 * if your hosting configuration (i.e. RAM allocation, CPU speed) allows for a
 * larger number of entities to be processed in a single batch run.
 */
$settings['entity_update_batch_size'] = 50;

/**
 * Image Style Settings
 *
 * We don't need `itok` DDoS protection in this firewalled environment.
 */
$config['image.settings']['allow_insecure_derivatives'] = TRUE;
$config['image.settings']['suppress_itok_output'] = TRUE;

/**
 * CMS Build settings.
 *
 * These are settings to trigger a static file, front-end WEB build job.
 * DevOps engineers will need to get and set the getenv() ENV variables below
 * from va.gov-cms-devops Ansible Vault for testing.
 * @see /README.md for details
 */
$settings['jenkins_build_env'] = getenv('CMS_ENVIRONMENT_TYPE') ?: FALSE;
$settings['jenkins_build_job_host'] = 'http://jenkins.vfs.va.gov';
// Authorized to the Jenkins API via GitHub login.
$settings['va_cms_bot_github_username'] = 'va-cms-bot';
$settings['va_cms_bot_github_auth_token'] = getenv('CMS_GITHUB_VA_CMS_BOT_TOKEN') ?: FALSE;

// Environment settings
$settings['va_gov_composer_home'] = getenv('COMPOSER_HOME');
$settings['va_gov_path_to_composer'] = '/usr/local/bin/composer';
// The default project root locations. These settings are currently only used on Tugboat and local environments.
$settings['va_gov_web_root'] = '/var/www/cms/web';
$settings['va_gov_app_root'] = '/var/www/cms';
$settings['va_gov_vets_website_root'] = '/var/www/cms/docroot/vendor/va-gov/vets-website';

// Defaults (should only be local that doesn't set these), default to dev for config_split
$config['config_split.config_split.dev']['status'] = TRUE;
$config['config_split.config_split.stg']['status'] = FALSE;
$config['config_split.config_split.prod']['status'] = FALSE;
$config['system.performance']['cache']['page']['use_internal'] = FALSE;
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['css']['gzip'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$config['system.performance']['js']['gzip'] = FALSE;
$config['system.performance']['response']['gzip'] = FALSE;
$config['views.settings']['ui']['show']['sql_query']['enabled'] = TRUE;
$config['views.settings']['ui']['show']['performance_statistics'] = TRUE;
$config['system.logging']['error_level'] = 'all';
$config['environment_indicator.indicator']['bg_color'] = '#05F901';
$config['environment_indicator.indicator']['fg_color'] = '#000000';
$config['environment_indicator.indicator']['name'] = 'Local';

$settings['config_sync_directory'] = '../config/sync';

$env_type = getenv('CMS_ENVIRONMENT_TYPE') ?: 'ci';

/**
 * Environment discovery service settings, vended by the Environment Discovery
 * service (`va_gov.environment_discovery`).
 */
$settings['va_gov_environment'] = [
  'environment_raw' => $env_type,
  'is_cms_test' => getenv('CMS_APP_NAME') ?? '' === 'cms-test',
];

$config['govdelivery_bulletins.settings']['govdelivery_endpoint'] = getenv('CMS_GOVDELIVERY_ENDPOINT') ?: FALSE;
$config['govdelivery_bulletins.settings']['govdelivery_username'] = getenv('CMS_GOVDELIVERY_USERNAME') ?: FALSE;
$config['govdelivery_bulletins.settings']['govdelivery_password'] = getenv('CMS_GOVDELIVERY_PASSWORD') ?: FALSE;
// Configuration for Mapbox
$config['geocoder.geocoder_provider.mapbox']['configuration']['accessToken'] = getenv('MAPBOX_TOKEN_CMS');

// Set migration settings from environment variables.
$facility_api_urls = [getenv('CMS_VAGOV_API_URL') . '/services/va_facilities/v0/facilities/all'];
$facility_api_key = getenv('CMS_VAGOV_API_KEY');
$facility_migrations = [
  'va_node_health_care_local_facility',
  'va_node_facility_vba',
  'va_node_facility_nca',
  'va_node_facility_vet_centers',
  'va_node_facility_vet_centers_mvc',
  'va_node_facility_vet_centers_os',
  //Add other migrations here
];

// Use classic node migrations instead of complete.
// cf. https://www.drupal.org/node/3105503
$settings['migrate_node_migrate_type_classic'] = TRUE;
foreach ($facility_migrations as $facility_migration) {
  $config["migrate_plus.migration.{$facility_migration}"]['source']['urls'] = $facility_api_urls;
  $config["migrate_plus.migration.{$facility_migration}"]['source']['headers']['apikey'] = $facility_api_key;
}

// HTTP client settings
$settings['http_client_config']['timeout'] = 60;

// Variables for post_api.
$settings['post_api_endpoint_host'] = getenv('CMS_VAGOV_API_URL') ?: FALSE;
$settings['post_api_apikey'] = getenv('CMS_VAGOV_API_KEY') ?: FALSE;

// Slack Webhook URL for csm-notifications channel.
$settings['slack_webhook_url'] = getenv('CMS_VAGOV_SLACK_WEBHOOK_URL') ?: FALSE;
$config['slack.settings']['slack_webhook_url'] = $settings['slack_webhook_url'];

// Settings supporting broken link report import. Off by default.
$settings['broken_link_report_import_enabled'] = FALSE;
// Default prod location, overrideable by env var.
$settings['broken_link_report_location'] = getenv('CONTENT_RELEASE_BROKEN_LINK_REPORT') ?: 'https://vetsgov-website-builds-s3-upload.s3-us-gov-west-1.amazonaws.com/broken-link-reports/vagovprod-broken-links.json';

// Hide deprecation warnings during transition to PHP 8.1.
$error_reporting = (int) ini_get('error_reporting');
ini_set('error_reporting', $error_reporting & ~E_DEPRECATED & ~E_USER_DEPRECATED);

$settings_files = [
  // Environment specific settings
  __DIR__ . '/settings/settings.' . $env_type . '.php',
  // Fast 404 settings
  __DIR__ . '/settings/settings.fast_404.php',
  // Ansible moves this file into place during deploy, so if it is present we are in deploy mode.
  __DIR__ . '/settings/settings.deploy.active.php',
  // Local overrides
  __DIR__ . '/settings.local.php',
];

foreach ($settings_files as $file) {
  if (file_exists($file)) {
    include $file;
  }
}

/**
 * Preserve control characters (e.g. newlines) in text passed to syslog.
 * @see https://bugs.php.net/bug.php?id=77913
 * TL;DR: Ensure each watchdog entry becomes only a single line in syslog.
 */
ini_set('syslog.filter', 'raw');

// The VA_GOV_IN_DEPLOY_MODE is set in settings.deploy.active.php.
// The file is copied from settings.deploy.inactive.php by Ansible during deploys.
if (!empty($GLOBALS['request']) &&
  is_a($GLOBALS['request'], \Symfony\Component\HttpFoundation\Request::class) &&
  !empty(getenv('VA_GOV_IN_DEPLOY_MODE'))) {

  $deploy_service = \Drupal\va_gov_backend\Deploy\DeployService::create();
  $deploy_service->run($GLOBALS['request'], $app_root, $site_path);
}

// Because Jenkins can't resolve e.g. prod.cms.va.gov DNS, and only the
// internal*elb addresses. And sometimes the host would return data with
// "http://default/..." hostnames for files so we set the host here and pass it
// to the `file_public_base_url` setting to fix that.
if (!empty($webhost_on_cli)) {
  if (PHP_SAPI === 'cli') {
    // This is running from drush so set the webhost.
    // Var $webhost_on_cli is set in <settings.<environment>.php.
    $webhost = $webhost_on_cli;
  }
  else {
    // This is running from an HTTP request.
    $webhost = "{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['HTTP_HOST']}";
  }

  $settings['file_public_base_url'] = "{$webhost}/sites/default/files";
}

// Monolog
$settings['container_yamls'][] = __DIR__ . '/services/services.monolog.yml';

// Memcache-specific settings
if ((extension_loaded('memcache') || extension_loaded('memcached')) && !empty($settings['memcache']['servers'])) {
  $settings['cache']['default'] = 'cache.backend.memcache';
  $settings['memcache']['bins'] = [
    'default' => 'default',
  ];
  $settings['container_yamls'][] = __DIR__ . '/services/services.memcache.yml';
  $settings['memcache']['persistent'] = 'drupal';
}

// Environment specific services container.
$env_services_path = __DIR__ . "/services/services.$env_type.yml";
if (file_exists($env_services_path)) {
  $settings['container_yamls'][] = $env_services_path;
}

// Global override for setting the session transaction isolation level.
// This is intended to prevent deadlocks in the course of normal operation.
// @see https://www.drupal.org/project/drupal/issues/2733675
$databases['default']['default']['init_commands']['isolation_level'] = 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED';
