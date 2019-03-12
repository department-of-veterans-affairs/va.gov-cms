<?php

// @codingStandardsIgnoreFile

$config['config_split.config_split.dev']['status'] = TRUE;
$config['config_split.config_split.stg']['status'] = FALSE;
$config['config_split.config_split.prod']['status'] = FALSE;
$config['config_split.config_split.config_dev']['status'] = TRUE;
$config['system.performance']['cache']['page']['use_internal'] = FALSE;
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['css']['gzip'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$config['system.performance']['js']['gzip'] = FALSE;
$config['system.performance']['response']['gzip'] = FALSE;
$config['views.settings']['ui']['show']['sql_query']['enabled'] = TRUE;
$config['views.settings']['ui']['show']['performance_statistics'] = TRUE;
$config['system.logging']['error_level'] = 'all';
$config['environment_indicator.indicator']['bg_color'] = '#ffffff';
$config['environment_indicator.indicator']['fg_color'] = '#000000';
$config['environment_indicator.indicator']['name'] = 'Local';

$settings['trusted_host_patterns'] = [
    '^pr.*\.va\.agile6\.com$',
    '^*\.us-gov-west-1\.elb\.amazonaws\.com$',
];
