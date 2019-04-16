<?php

// @codingStandardsIgnoreFile

// Disabled for now, not sure we want to trigger PROD just yet. Update array in
// modules/custom/va_gov_build_trigger/src/Form/BuildTriggerForm.php:35 too.
// $settings['va_jenkins_build_job_url_params'] = '/job/deploys/job/vets-gov-autodeploy-vets-website/buildWithParameters?' . 'release_wait=5' . '&' . 'use_latest_release=1';

$config['config_split.config_split.dev']['status'] = FALSE;
$config['config_split.config_split.stg']['status'] = FALSE;
$config['config_split.config_split.prod']['status'] = TRUE;
$config['system.performance']['cache']['page']['use_internal'] = TRUE;
$config['system.performance']['css']['preprocess'] = TRUE;
$config['system.performance']['css']['gzip'] = TRUE;
$config['system.performance']['js']['preprocess'] = TRUE;
$config['system.performance']['js']['gzip'] = TRUE;
$config['system.performance']['response']['gzip'] = TRUE;
$config['views.settings']['ui']['show']['sql_query']['enabled'] = FALSE;
$config['views.settings']['ui']['show']['performance_statistics'] = FALSE;
$config['system.logging']['error_level'] = 'none';
$config['environment_indicator.indicator']['bg_color'] = '#ff2301';
$config['environment_indicator.indicator']['fg_color'] = '#000000';
$config['environment_indicator.indicator']['name'] = 'Production';

$settings['trusted_host_patterns'] = [
    // For ALB/ELB Healthchecks.
    '10\.199.*',
    '10\.247.*',
    'localhost',
    '^cms\.va\.gov$',
    '^prod\.cms\.va\.gov$',
    '^.*\.us-gov-west-1\.elb\.amazonaws\.com$',
];

if (file_exists($app_root . '/' . $site_path . '/settings/settings.fast_404.php')) {
    include $app_root . '/' . $site_path . '/settings/settings.fast_404.php';
}
