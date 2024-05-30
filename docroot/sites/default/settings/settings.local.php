<?php

// phpcs:ignoreFile

// This should be set to something different, e.g. 'lando', if not in DDEV.
//
// We should shift to using the string 'ddev' rather than 'local' in the future
// to avoid confusion (especially given that we can have local overrides on
// _any_ environment).
$settings['va_gov_environment']['environment'] = 'ddev';

$settings['jenkins_build_job_path'] = '/job/builds/job/vets-website-content-vagov' . $settings['jenkins_build_env'];
$settings['jenkins_build_job_params'] = '/buildWithParameters?deploy=true';
$settings['jenkins_build_job_url'] = $settings['jenkins_build_job_host'] . $settings['jenkins_build_job_path'] . $settings['jenkins_build_job_params'];
$settings['skip_permissions_hardening'] = TRUE;

$config['config_split.config_split.dev']['status'] = FALSE;
$config['config_split.config_split.stg']['status'] = FALSE;
$config['config_split.config_split.prod']['status'] = FALSE;
$config['config_split.config_split.local']['status'] = TRUE;
$config['config_split.config_split.tugboat']['status'] = FALSE;

$config['system.performance']['cache']['page']['use_internal'] = FALSE;
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['css']['gzip'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$config['system.performance']['js']['gzip'] = FALSE;
$config['system.performance']['response']['gzip'] = FALSE;
$config['views.settings']['ui']['show']['sql_query']['enabled'] = TRUE;
$config['views.settings']['ui']['show']['performance_statistics'] = TRUE;
$config['system.logging']['error_level'] = 'all';
$config['environment_indicator.indicator']['bg_color'] = '#0B5512'; // dark green.
$config['environment_indicator.indicator']['fg_color'] = '#ffffff';
$config['environment_indicator.indicator']['name'] = 'ddev';

$webhost_on_cli = 'https://va-gov-cms.ddev.site';
$settings['webhost'] = $webhost_on_cli;

// Link to this file locally since local can not access prod where the real
// file exists.  You will need to copy the file from the same path on prod.
$config['migrate_plus.migration.va_node_form']['source']['urls'] = ['https://va-gov-cms.ddev.site/sites/default/files/migrate_source/va_forms_data.csv'];

$settings['trusted_host_patterns'] = ['.*'];

$settings['va_gov_frontend_build_type'] = 'local';
$settings['va_gov_frontend_url'] = $webhost_on_cli . '/static';
$settings['va_gov_app_root'] = getenv('DDEV_APPROOT');
$settings['va_gov_web_root'] = getenv('DDEV_APPROOT') . '/web';
$settings['va_gov_vets_website_root'] = getenv('DDEV_APPROOT') . '/docroot/vendor/va-gov/vets-website';

$settings['memcache']['servers'] = [
  'memcached:11211' => 'default',
];

$settings['cms_datadog_api_key'] = getenv('CMS_DATADOG_API_KEY');

$settings['va_cms_bot_github_auth_token'] = getenv('GITHUB_TOKEN') ?: 'fake-token';

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

  // Setting the custom header to switch the file base URL in next-build
  // Uncomment this next line to test that the header is being set
  header('File-Public-Base-Url-Check: true');

  // Get all headers
  $headersArray = headers_list();

  // If the header is set in the response headers
  if (in_array('File-Public-Base-Url-Check: true', $headersArray, true)) {
    // Make the file base url point to staging
    $settings['file_public_base_url'] = "https://dsva-vagov-staging-cms-files.s3.us-gov-west-1.amazonaws.com";
  } else {
    // Otherwise use the default webhost
    $settings['file_public_base_url'] = "{$webhost}/sites/default/files";
  }
}
