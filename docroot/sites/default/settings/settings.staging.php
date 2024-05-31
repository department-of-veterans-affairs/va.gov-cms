<?php

// phpcs:ignoreFile

$settings['va_gov_environment']['environment'] = 'staging';

include dirname(__FILE__) . '/settings.brd_common.php';

$settings['jenkins_build_job_path'] = '/job/builds/job/content-build-content-only-vagov' . $settings['jenkins_build_env'];
$settings['jenkins_build_job_params'] = '/buildWithParameters?deploy=true';
$settings['jenkins_build_job_url'] = $settings['jenkins_build_job_host'] . $settings['jenkins_build_job_path'] . $settings['jenkins_build_job_params'];

$config['config_split.config_split.dev']['status'] = FALSE;
$config['config_split.config_split.stg']['status'] = TRUE;
$config['config_split.config_split.prod']['status'] = FALSE;
$config['config_split.config_split.local']['status'] = FALSE;
$config['config_split.config_split.tugboat']['status'] = FALSE;

$config['system.performance']['cache']['page']['use_internal'] = TRUE;
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['css']['gzip'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$config['system.performance']['js']['gzip'] = FALSE;
$config['system.performance']['response']['gzip'] = TRUE;
$config['views.settings']['ui']['show']['sql_query']['enabled'] = FALSE;
$config['views.settings']['ui']['show']['performance_statistics'] = FALSE;
$config['system.logging']['error_level'] = 'none';
$config['environment_indicator.indicator']['bg_color'] = '#F9C642';
$config['environment_indicator.indicator']['fg_color'] = '#212121';
$config['environment_indicator.indicator']['name'] = 'Staging';

$webhost_on_cli = 'https://staging.cms.va.gov';
$settings['webhost'] = $webhost_on_cli;

$settings['trusted_host_patterns'] = [
    // For ALB/ELB Healthchecks.
    '10\.199.*',
    '10\.247.*',
    'localhost',
    'va-gov-cms.ddev.site',
    'stg.cms.va.gov',
    'staging.cms.va.gov',
    'test.staging.cms.va.gov',
    '.*\.us-gov-west-1\.elb\.amazonaws\.com',
];

$settings['va_gov_frontend_url'] = 'https://staging.va.gov';
$settings['va_gov_frontend_build_type'] = 'brd';
$settings['github_actions_deploy_env'] = 'staging';

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
      // Make the file base url point to staging
      $settings['file_public_base_url'] = "https://dsva-vagov-staging-cms-files.s3.us-gov-west-1.amazonaws.com";
    } else {
      // Otherwise use the default webhost
      $settings['file_public_base_url'] = "{$webhost}/sites/default/files";
    }
  }
