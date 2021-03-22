<?php

// @codingStandardsIgnoreFile

/**
 * For documentation and more options
 * @see https://git.drupalcode.org/project/drupal/blob/8.6.x/sites/default/default.settings.php
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
  // 'database' is the default DB container for Lando (local).
  'host' => getenv('CMS_MARIADB_HOST') ?: 'database',
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
$env_type = getenv('CMS_ENVIRONMENT_TYPE') ?: 'ci';

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/../default/services.yml';
// Environment specific services
if (file_exists($app_root . '/' . $site_path . '/services/services.' . $env_type . '.yml')) {
  $settings['container_yamls'][] = $app_root . '/' . $site_path . '/services/services.' . $env_type . '.yml';
}

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
// The default BRD locations. These settings are currently only used on tugboat/lando
$settings['va_gov_web_root'] = '/var/www/cms/docroot/web';
$settings['va_gov_app_root'] = '/var/www/cms';

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

$config['govdelivery_bulletins.settings']['govdelivery_endpoint'] = getenv('CMS_GOVDELIVERY_ENDPOINT') ?: FALSE;
$config['govdelivery_bulletins.settings']['govdelivery_username'] = getenv('CMS_GOVDELIVERY_USERNAME') ?: FALSE;
$config['govdelivery_bulletins.settings']['govdelivery_password'] = getenv('CMS_GOVDELIVERY_PASSWORD') ?: FALSE;

// Set migration settings from environment variables.
$facility_api_urls = [getenv('CMS_VAGOV_API_URL') . '/services/va_facilities/v0/facilities/all'];
$facility_api_key = getenv('CMS_VAGOV_API_KEY');
$facility_migrations = [
  'va_node_health_care_local_facility',
  'va_node_facility_vba',
  'va_node_facility_nca',
  'va_node_facility_vet_centers',
];

// Use classic node migrations instead of complete.
// cf. https://www.drupal.org/node/3105503
$settings['migrate_node_migrate_type_classic'] = TRUE;
foreach ($facility_migrations as $facility_migration) {
  $config["migrate_plus.migration.{$facility_migration}"]['source']['urls'] = $facility_api_urls;
  $config["migrate_plus.migration.{$facility_migration}"]['source']['headers']['apikey'] = $facility_api_key;
}

// Variables for post_api.
$settings['post_api_endpoint_host'] = getenv('CMS_VAGOV_API_URL') ?: FALSE;
$settings['post_api_apikey'] = getenv('CMS_VAGOV_API_KEY') ?: FALSE;

// Slack Webhook URL for csm-notifications channel.
$settings['slack_webhook_url'] = getenv('CMS_VAGOV_SLACK_WEBHOOK_URL') ?: FALSE;
$config['slack.settings']['slack_webhook_url'] = $settings['slack_webhook_url'];

// Environment specific settings
if (file_exists($app_root . '/' . $site_path . '/settings/settings.' . $env_type . '.php')) {
  include $app_root . '/' . $site_path . '/settings/settings.' . $env_type . '.php';
}

// Fast 404 settings
if (file_exists($app_root . '/' . $site_path . '/settings/settings.fast_404.php')) {
  include $app_root . '/' . $site_path . '/settings/settings.fast_404.php';
}

// Ansible moves this file into place during deploy, so if it is present we are in deploy mode.
if (file_exists($app_root . '/' . $site_path . '/settings/settings.deploy.active.php')) {
  include $app_root . '/' . $site_path . '/settings/settings.deploy.active.php';
}

/**
 * Load local development override configuration, if available.
 *
 * Use settings.local.php to override variables on secondary (staging,
 * development, etc) installations of this site. Typically used to disable
 * caching, JavaScript/CSS compression, re-routing of outgoing emails, and
 * other things that should not happen on development and testing sites.
 *
 * Keep this code block at the end of this file to take full effect.
 */
// Local settings, must stay at bottom of file, this file is ignored by git.
if (file_exists($app_root . '/' . $site_path . '/settings/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings/settings.local.php';
}

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
  // DevShop sets this in settings.devshop.php
  $settings['file_public_base_url'] = "{$webhost}/sites/default/files";
}

// Disable use of the Symfony autoloader, and use the Composer autoloader instead.
$settings['class_loader_auto_detect'] = FALSE;
