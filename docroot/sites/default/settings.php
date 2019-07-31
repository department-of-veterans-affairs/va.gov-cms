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

//@todo Delete config_directories, not used.
$config_directories = [];

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
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';

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
$config['config_split.config_split.config_dev']['status'] = FALSE;
$config_directories['sync'] = '../config/sync';

$env_type = getenv('CMS_ENVIRONMENT_TYPE') ?: 'local';

// Environment specific settings
if (file_exists($app_root . '/' . $site_path . '/settings/settings.' . $env_type . '.php')) {
    include $app_root . '/' . $site_path . '/settings/settings.' . $env_type . '.php';
}

// SimpleSAMLphp configuration
# Provide universal absolute path to the installation.
$simplesamlphp_dir = 'simplesamlphp-1.17.2';
if (is_dir(DRUPAL_ROOT . '/../' . $simplesamlphp_dir)) {
    $settings['simplesamlphp_dir'] = DRUPAL_ROOT . '/../' . $simplesamlphp_dir;
}
# SimpleSAMLphp Auth module settings
$config['simplesamlphp_auth.settings'] = [
    // Basic settings.
    'activate'                => TRUE, // Enable or Disable SAML login.
    'auth_source'             => 'default-sp',
    'login_link_display_name' => 'Login with your SSOi account.',
    'register_users'          => TRUE,
    'debug'                   => TRUE,
    // Local authentication.
    'allow' => [
        'default_login'         => TRUE,
        'set_drupal_pwd'        => TRUE,
        'default_login_users'   => '1',
        'default_login_roles'   => [
            'authenticated' => FALSE,
            'administrator' => 'administrator',
            'content_api_consumer' => 'content_api_consumer',
            'content_editor' => 'content_editor',
            'content_reviewer' => 'content_reviewer',
            'content_publisher' => 'content_publisher',
            'admnistrator_users' => 'admnistrator_users',
            'administrator' => 'administrator',
            'redirect_administrator' => 'redirect_administrator',
            'documentation_editor' => FALSE,
        ],
    ],
    'logout_goto_url'         => '',
    // User info and syncing.
    // `unique_id` is specified in Transient format, otherwise this should be `UPN`
    // Please talk to your SSO adminsitrators about which format you should be using.
    'unique_id'               => 'VAUID',
    'user_name'               => 'adUPN',
    'mail_attr'               => 'adUPN',
    'sync' => [
        'mail'      => TRUE,
        'user_name' => TRUE,
    ],
];

// Fast 404 settings
if (file_exists($app_root . '/' . $site_path . '/settings/settings.fast_404.php')) {
  include $app_root . '/' . $site_path . '/settings/settings.fast_404.php';
}

// Local settings, must stay at bottom of file, this file is ignored by git.
if (file_exists($app_root . '/' . $site_path . '/settings/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings/settings.local.php';
}

