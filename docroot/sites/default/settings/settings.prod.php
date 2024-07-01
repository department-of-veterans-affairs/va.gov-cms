<?php

// phpcs:ignoreFile

$settings['va_gov_environment']['environment'] = 'prod';

include dirname(__FILE__) . '/settings.brd_common.php';

$settings['jenkins_build_job_path'] = '/job/deploys/job/vets-gov-autodeploy-content-build';
$settings['jenkins_build_job_params'] = '/buildWithParameters?' . 'release_wait=0' . '&' . 'use_latest_release=true';
$settings['jenkins_build_job_url'] = $settings['jenkins_build_job_host'] . $settings['jenkins_build_job_path'] . $settings['jenkins_build_job_params'];

$config['config_split.config_split.dev']['status'] = FALSE;
$config['config_split.config_split.stg']['status'] = FALSE;
$config['config_split.config_split.prod']['status'] = TRUE;
$config['config_split.config_split.local']['status'] = FALSE;
$config['config_split.config_split.tugboat']['status'] = FALSE;

$config['system.performance']['cache']['page']['use_internal'] = TRUE;
$config['system.performance']['css']['preprocess'] = TRUE;
$config['system.performance']['css']['gzip'] = TRUE;
$config['system.performance']['js']['preprocess'] = TRUE;
$config['system.performance']['js']['gzip'] = TRUE;
$config['system.performance']['response']['gzip'] = TRUE;
$config['views.settings']['ui']['show']['sql_query']['enabled'] = FALSE;
$config['views.settings']['ui']['show']['performance_statistics'] = FALSE;
$config['system.logging']['error_level'] = 'none';
$config['environment_indicator.indicator']['bg_color'] = '#112E51';
$config['environment_indicator.indicator']['fg_color'] = '#ffffff';
$config['environment_indicator.indicator']['name'] = 'Production';

$webhost_on_cli = 'https://prod.cms.va.gov';
$settings['webhost'] = $webhost_on_cli;

$settings['trusted_host_patterns'] = [
    // For ALB/ELB Healthchecks.
    '10\.199.*',
    '10\.247.*',
    'localhost',
    'va-gov-cms.ddev.site',
    'prod.cms.va.gov',
    'test.prod.cms.va.gov',
    'cms.va.gov',
    '.*\.us-gov-west-1\.elb\.amazonaws\.com',
];

$settings['va_gov_frontend_url'] = 'https://www.va.gov';
$settings['va_gov_frontend_build_type'] = 'brd';
$settings['github_actions_deploy_env'] = 'prod';

// Settings supporting broken link report import.
$settings['broken_link_report_import_enabled'] = TRUE;

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
    // header('File-Public-Base-Url-Check: true');
  
    // Get all headers
    $headersArray = headers_list();
  
    // If the header is set in the response headers
    if (in_array('File-Public-Base-Url-Check: true', $headersArray, true)) {
      // Make the file base url point to prod
      $settings['file_public_base_url'] = "https://dsva-vagov-prod-cms-files.s3.us-gov-west-1.amazonaws.com";
    } else {
      // Otherwise use the default webhost
      $settings['file_public_base_url'] = "{$webhost}/sites/default/files";
    }
  }
