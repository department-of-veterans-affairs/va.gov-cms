<?php

// phpcs:ignoreFile

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
$config['environment_indicator.indicator']['bg_color'] = '#082142'; // Dark blue.
$config['environment_indicator.indicator']['fg_color'] = '#ffffff';
$config['environment_indicator.indicator']['name'] = 'Production';

$webhost_on_cli = 'https://prod.cms.va.gov';

$settings['trusted_host_patterns'] = [
    // For ALB/ELB Healthchecks.
    '10\.199.*',
    '10\.247.*',
    'localhost',
    'va-gov-cms.lndo.site',
    'prod.cms.va.gov',
    'test.prod.cms.va.gov',
    'cms.va.gov',
    '.*\.us-gov-west-1\.elb\.amazonaws\.com',
];

$settings['va_gov_frontend_build_type'] = 'brd';
$settings['va_gov_frontend_url'] = 'https://www.va.gov';
