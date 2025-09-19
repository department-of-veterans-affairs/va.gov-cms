<?php

// phpcs:ignoreFile

include dirname(__FILE__) . '/settings.brd_common.php';

$settings['va_gov_environment']['environment'] = 'dev';

$settings['jenkins_build_job_path'] = '/job/builds/job/content-build-content-only-vagov' . $settings['jenkins_build_env'];
$settings['jenkins_build_job_params'] = '/buildWithParameters?deploy=true';
$settings['jenkins_build_job_url'] = $settings['jenkins_build_job_host'] . $settings['jenkins_build_job_path'] . $settings['jenkins_build_job_params'];

$config['config_split.config_split.dev']['status'] = TRUE;
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
$config['views.settings']['ui']['show']['sql_query']['enabled'] = TRUE;
$config['views.settings']['ui']['show']['performance_statistics'] = TRUE;
$config['system.logging']['error_level'] = 'all';
$config['environment_indicator.indicator']['bg_color'] = '#5900CA'; // Purple.
$config['environment_indicator.indicator']['fg_color'] = '#ffffff';
$config['environment_indicator.indicator']['name'] = 'Development';

$webhost_on_cli = 'https://dev.cms.va.gov';
$settings['webhost'] = $webhost_on_cli;

$settings['trusted_host_patterns'] = [
    // For ALB/ELB Healthchecks.
    '10\.199.*',
    '10\.247.*',
    'localhost',
    'dev.cms.va.gov',
    'test.dev.cms.va.gov',
    '.*\.us-gov-west-1\.elb\.amazonaws\.com',
    'va-gov-cms.ddev.site',
];

$settings['va_gov_frontend_url'] = 'https://dev.va.gov';
$settings['va_gov_frontend_build_type'] = 'brd';
$settings['github_actions_deploy_env'] = 'dev';

// Public asset S3 location
$public_asset_s3_base_url = "https://dsva-vagov-staging-cms-files.s3.us-gov-west-1.amazonaws.com";

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

$settings['memcache']['bins'] = [
  'default' => 'default',
  'bootstrap' => 'default',
  'config' => 'default',
  'discovery' => 'default',
  'menu' => 'default',
  'render' => 'default',
  'data' => 'default',
  'toolbar' => 'default',
];

// Memcached options for optimal performance
$settings['memcache']['options'] = [
  \Memcached::OPT_COMPRESSION => TRUE,
  \Memcached::OPT_DISTRIBUTION => \Memcached::DISTRIBUTION_CONSISTENT,
  \Memcached::OPT_LIBKETAMA_COMPATIBLE => TRUE,
  \Memcached::OPT_HASH => \Memcached::HASH_MD5,
  \Memcached::OPT_TCP_NODELAY => TRUE,
  \Memcached::OPT_CONNECT_TIMEOUT => 1000,
  \Memcached::OPT_SEND_TIMEOUT => 3000000,
  \Memcached::OPT_RECV_TIMEOUT => 3000000,
  \Memcached::OPT_POLL_TIMEOUT => 1000,
  \Memcached::OPT_RETRY_TIMEOUT => 5,
  \Memcached::OPT_SERVER_FAILURE_LIMIT => 2,
];

// Cache backend configuration
$settings['cache']['default'] = 'cache.backend.memcache';

// Key prefix to avoid conflicts
$settings['memcache']['key_prefix'] = 'va_cms_dev';

// Memcache stampede protection
$settings['memcache']['stampede_protection'] = TRUE;
$settings['memcache']['stampede_semaphore'] = 15;
$settings['memcache']['stampede_wait_time'] = 5;
$settings['memcache']['stampede_wait_limit'] = 3;
