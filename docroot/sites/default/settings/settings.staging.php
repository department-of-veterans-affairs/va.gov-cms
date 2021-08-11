<?php

// phpcs:ignoreFile

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
$config['system.performance']['css']['preprocess'] = TRUE;
$config['system.performance']['css']['gzip'] = TRUE;
$config['system.performance']['js']['preprocess'] = TRUE;
$config['system.performance']['js']['gzip'] = TRUE;
$config['system.performance']['response']['gzip'] = TRUE;
$config['views.settings']['ui']['show']['sql_query']['enabled'] = FALSE;
$config['views.settings']['ui']['show']['performance_statistics'] = FALSE;
$config['system.logging']['error_level'] = 'none';
$config['environment_indicator.indicator']['bg_color'] = '#FEBB08'; // $color-gold-light.
$config['environment_indicator.indicator']['fg_color'] = '#000000';
$config['environment_indicator.indicator']['name'] = 'Staging';

$webhost_on_cli = 'https://staging.cms.va.gov';

$settings['trusted_host_patterns'] = [
    // For ALB/ELB Healthchecks.
    '10\.199.*',
    '10\.247.*',
    'localhost',
    'va-gov-cms.lndo.site',
    'stg.cms.va.gov',
    'staging.cms.va.gov',
    'test.staging.cms.va.gov',
    '.*\.us-gov-west-1\.elb\.amazonaws\.com',
];

$settings['va_gov_frontend_url'] = 'https://staging.va.gov';
$settings['va_gov_frontend_build_type'] = 'brd';
