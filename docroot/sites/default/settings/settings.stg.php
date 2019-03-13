<?php

// @codingStandardsIgnoreFile

$config['config_split.config_split.dev']['status'] = FALSE;
$config['config_split.config_split.stg']['status'] = TRUE;
$config['config_split.config_split.prod']['status'] = FALSE;
$config['system.performance']['cache']['page']['use_internal'] = TRUE;
$config['system.performance']['css']['preprocess'] = TRUE;
$config['system.performance']['css']['gzip'] = TRUE;
$config['system.performance']['js']['preprocess'] = TRUE;
$config['system.performance']['js']['gzip'] = TRUE;
$config['system.performance']['response']['gzip'] = TRUE;
$config['views.settings']['ui']['show']['sql_query']['enabled'] = FALSE;
$config['views.settings']['ui']['show']['performance_statistics'] = FALSE;
$config['system.logging']['error_level'] = 'none';
$config['environment_indicator.indicator']['bg_color'] = '#fffb03';
$config['environment_indicator.indicator']['fg_color'] = '#000000';
$config['environment_indicator.indicator']['name'] = 'Staging';

$settings['trusted_host_patterns'] = [
    // For ELB Healthchecks.
    '10\.199.*',
    'localhost',
    '^stg\.va\.agile6\.com$',
    '^staging\.va\.agile6\.com$',
    '^stg\.cms\.va\.gov$',
    '^staging\.cms\.va\.gov$',
];

if (file_exists($app_root . '/' . $site_path . '/settings/settings.fast_404.php')) {
    include $app_root . '/' . $site_path . '/settings/settings.fast_404.php';
}
