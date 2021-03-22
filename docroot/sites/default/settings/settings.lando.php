<?php

// @codingStandardsIgnoreFile

$settings['jenkins_build_job_path'] = '/job/builds/job/vets-website-content-vagov' . $settings['jenkins_build_env'];
$settings['jenkins_build_job_params'] = '/buildWithParameters?deploy=true';
$settings['jenkins_build_job_url'] = $settings['jenkins_build_job_host'] . $settings['jenkins_build_job_path'] . $settings['jenkins_build_job_params'];
$settings['skip_permissions_hardening'] = TRUE;

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
$config['environment_indicator.indicator']['bg_color'] = '#0B5512'; // dark green.
$config['environment_indicator.indicator']['fg_color'] = '#ffffff';
$config['environment_indicator.indicator']['name'] = 'Lando';

$webhost_on_cli = 'http://va-gov-cms.lndo.site';

// Link to this file locally since lando can not access prod where the real
// file exists.  You will need to copy the file from the same path on prod.
$config['migrate_plus.migration.va_node_form']['source']['urls'] = ['http://va-gov-cms.lndo.site/sites/default/files/migrate_source/va_forms_data.csv'];

$settings['trusted_host_patterns'] = [
    'localhost',
    '127.0.0.1',
    'va-gov-cms.lndo.site',
];

$settings['va_gov_frontend_build_type'] = 'lando';
$settings['va_gov_frontend_url'] = $webhost_on_cli . '/static';
$settings['va_gov_app_root'] = getenv('LANDO_MOUNT');
$settings['va_gov_web_root'] = getenv('LANDO_MOUNT') . '/web';

// Memcache
$settings['cache']['default'] = 'cache.backend.memcache';
$settings['memcache']['servers'] = ['memcache:11211' => 'default'];
