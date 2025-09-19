<?php

// phpcs:ignoreFile

include dirname(__FILE__) . '/settings.brd_common.php';

$config['config_split.config_split.dev']['status'] = FALSE;
$config['config_split.config_split.stg']['status'] = FALSE;
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
$config['environment_indicator.indicator']['bg_color'] = '#800080'; // Deep Purple
$config['environment_indicator.indicator']['fg_color'] = '#ffffff';
$config['environment_indicator.indicator']['name'] = 'EKS';

$webhost_on_cli = 'https://prod.cms.va.gov';

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
    'eks-dev\.cms\.va\.gov',
];

$settings['va_gov_frontend_build_type'] = 'eks';
$settings['va_gov_frontend_url'] = 'http://localhost:8080';

// Entra ID settings
$settings['microsoft_entra_id_client_id'] = getenv('MICROSOFT_ENTRA_ID_CLIENT_ID');
$settings['microsoft_entra_id_client_secret'] = getenv('MICROSOFT_ENTRA_ID_CLIENT_SECRET');
$settings['microsoft_entra_id_tenant_id'] = getenv('MICROSOFT_ENTRA_ID_TENANT_ID');

# Trust the reverse proxy.
# You may need to replace '127.0.0.1' with your Traefik pod's IP or a broader internal network range.
$settings['reverse_proxy'] = TRUE;
// Allow trusted proxy addresses to be set via environment variable, or use common internal ranges by default.
$trusted_proxy_env = getenv('TRUSTED_PROXY_ADDRESSES');
if ($trusted_proxy_env) {
  $settings['reverse_proxy_addresses'] = array_map('trim', explode(',', $trusted_proxy_env));
} else {
  $settings['reverse_proxy_addresses'] = [
    '127.0.0.1',
    '10.0.0.0/8',
    '172.16.0.0/12',
    '192.168.0.0/16',
  ];
}

# Set the HTTP protocol to use the X-Forwarded-Proto header.
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
  $_SERVER['HTTPS'] = 'on';
  $_SERVER['SERVER_PORT'] = 443;
}
