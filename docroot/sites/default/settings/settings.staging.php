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

// Public asset S3 location
$public_asset_s3_base_url = "https://dsva-vagov-staging-cms-files.s3.us-gov-west-1.amazonaws.com";

//S3FS settings
  $settings['s3fs.access_key'] = getenv('CMS_PDF_SERVICE_ACCT_KEY');
  $settings['s3fs.secret_key'] = getenv('CMS_PDF_SERVICE_ACCT_SECRET');
  $config['s3fs.settings']['bucket'] = 'dsva-vagov-staging-cms-pdf-archive';
  if (getenv('CMS_DRUPAL_ADDRESS') === 'https://test.staging.cms.va.gov') {
    $config['s3fs.settings']['bucket'] = 'dsva-vagov-staging-cms-test-pdf-archive';
  }
  $config['s3fs.settings']['region'] = 'us-gov-west-1';
  $settings['s3fs.use_s3_for_public'] = FALSE;
  $settings['s3fs.use_s3_for_private'] = FALSE;
  $settings['s3fs.upload_as_private'] = TRUE;
