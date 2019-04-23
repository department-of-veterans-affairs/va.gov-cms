<?php

// @codingStandardsIgnoreFile

$settings['va_jenkins_build_job_url_params'] = $settings['va_jenkins_build_job_dev_staging'] . '/buildWithParameters?cmsEnvBuildOverride=' . $settings['va_jenkins_build_env'];

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
$config['environment_indicator.indicator']['name'] = 'Development';

$settings['trusted_host_patterns'] = [
    // For ALB/ELB Healthchecks.
    '10\.199.*',
    '10\.247.*',
    'localhost',
    '^dev\.va\.agile6\.com$',
    '^dev\.cms\.va\.gov$',
    '^.*\.us-gov-west-1\.elb\.amazonaws\.com$',
];

if (file_exists($app_root . '/' . $site_path . '/settings/settings.fast_404.php')) {
  include $app_root . '/' . $site_path . '/settings/settings.fast_404.php';
}
