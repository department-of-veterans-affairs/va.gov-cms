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

// Restrict to known hosts (anchored and escaped).
$settings['trusted_host_patterns'] = [
    // For ALB/ELB Healthchecks (by IP as Host header).
    '^10\.199\..*$',
    '^10\.247\..*$',
    '^localhost$',
    '^dev\.cms\.va\.gov$',
    '^test\.dev\.cms\.va\.gov$',
    '^.*\.us-gov-west-1\.elb\.amazonaws\.com$',
    '^va-gov-cms\.ddev\.site$',
    '^eks-dev\.cms\.va\.gov$',
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

# Set the HTTP protocol to use the X-Forwarded-Proto header.
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
  $_SERVER['HTTPS'] = 'on';
  $_SERVER['SERVER_PORT'] = 443;
}

// Reverse proxy configuration
$settings['reverse_proxy'] = TRUE;
// Allow trusted proxy addresses to be set via environment variable, or use common Kubernetes/private ranges by default.
$trusted_proxy_env = getenv('TRUSTED_PROXY_ADDRESSES');
if ($trusted_proxy_env) {
  $settings['reverse_proxy_addresses'] = array_map('trim', explode(',', $trusted_proxy_env));
} else {
  $settings['reverse_proxy_addresses'] = [
    '127.0.0.1',     // Local proxy/ingress on same host.
    '::1',           // IPv6 localhost (add this to support IPv6 FPM sockets)
    '10.0.0.0/8',    // Common Kubernetes/cluster/private ranges (includes 10.96.0.0/12).
    '172.16.0.0/12', // Private ranges often used by clusters/subnets.
    '192.168.0.0/16' // Private ranges for on-prem/lab clusters.
  ];
}

// Trust ALL X-Forwarded-* headers by default to ensure proper proxy chain functionality
$settings['reverse_proxy_trusted_headers'] = \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_FOR
  | \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PORT
  | \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PROTO
  | \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_HOST;

// Opt-in: trust RFC 7239 Forwarded header if explicitly enabled.
if (filter_var(getenv('TRUST_FORWARDED_HEADER'), FILTER_VALIDATE_BOOLEAN)) {
  $settings['reverse_proxy_trusted_headers'] |= \Symfony\Component\HttpFoundation\Request::HEADER_FORWARDED;
}

// FPM header handling - ensure we properly handle PHP-FPM headers
if (!empty($_SERVER['HTTP_X_PHP_FPM_POOL'])) {
  // For internal tracking/debugging of which FPM pool handled the request
  $GLOBALS['fpm_pool'] = $_SERVER['HTTP_X_PHP_FPM_POOL'];
}

// Always trust X-Forwarded-Host and sanitize it properly
if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
  $tokens = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_HOST']));
  // Use the first non-empty value from the header
  $selected = reset($tokens);
  if ($selected) {
    $_SERVER['HTTP_X_FORWARDED_HOST'] = $selected;
  }
}

// Also sanitize the live Request's header bag if available (Request is created before settings load).
if (!empty($GLOBALS['request']) && $GLOBALS['request'] instanceof \Symfony\Component\HttpFoundation\Request) {
  $xfh = $GLOBALS['request']->headers->get('x-forwarded-host');
  if ($xfh) {
    $tokens = array_map('trim', explode(',', $xfh));
    $selected = reset($tokens);
    if ($selected) {
      $GLOBALS['request']->headers->set('x-forwarded-host', $selected);
    }
  }
}
// Map important bins to Memcache; keep 'form' in DB to avoid stampedes.
$settings['memcache']['bins'] = [
  'bootstrap' => 'default',
  'discovery' => 'default',
  'config'    => 'default',
  'render'    => 'default',
  'data'      => 'default',
];
$settings['cache']['bins']['form'] = 'cache.backend.database';
$settings['cache']['bins']['cachetags'] = 'cache.backend.database';

// Use a persistent ID so each FPM worker reuses the same connection pool and hash ring.
$settings['memcache']['persistent'] = TRUE;
$settings['memcache']['persistent_id'] = 'va_cms_dev_ring';

// Memcached client options: tight timeouts, failover, consistent hashing.
$settings['memcache']['options'] = [
  Memcached::OPT_BINARY_PROTOCOL   => true,
  Memcached::OPT_COMPRESSION       => false,

  // Timeouts are in milliseconds.
  Memcached::OPT_CONNECT_TIMEOUT   => 100,  // Fast connect fail.
  Memcached::OPT_RETRY_TIMEOUT     => 1,    // Recheck failed server quickly.
  Memcached::OPT_SEND_TIMEOUT      => 200,
  Memcached::OPT_RECV_TIMEOUT      => 200,
  Memcached::OPT_POLL_TIMEOUT      => 50,

  // Networking and failover.
  Memcached::OPT_TCP_NODELAY       => true,
  Memcached::OPT_SERVER_FAILURE_LIMIT => 2,
  Memcached::OPT_DEAD_TIMEOUT      => 15,

  // Consistent hashing for stable key distribution.
  Memcached::OPT_DISTRIBUTION      => Memcached::DISTRIBUTION_CONSISTENT,
  Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
];

// Cache backend configuration
$settings['cache']['default'] = 'cache.backend.memcache';

// Key prefix to avoid conflicts
$settings['memcache']['key_prefix'] = 'va_cms_dev';

// Optional: capture all request headers and send to Drupal watchdog for debugging.
// Enable by setting the environment variable LOG_REQUEST_HEADERS=true.
if (filter_var(getenv('LOG_REQUEST_HEADERS'), FILTER_VALIDATE_BOOLEAN)) {
  try {
    $headers = [];
    if (function_exists('getallheaders')) {
      $raw_headers = getallheaders();
      // Normalize header names to standard format.
      foreach ($raw_headers as $name => $value) {
        $headers[$name] = $value;
      }
    }
    else {
      // Fall back to $_SERVER parsing for environments without getallheaders().
      foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0) {
          $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
          $headers[$name] = $value;
        }
        // Also include the special CONTENT_* headers.
        if (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], TRUE)) {
          $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
          $headers[$name] = $value;
        }
      }
    }

    // Ensure values are strings and truncate very long values to avoid enormous log entries.
    array_walk($headers, function (&$v) {
      if (is_array($v)) {
        $v = json_encode($v);
      } else {
        $v = (string) $v;
      }
      // Truncate each header value to 4000 characters.
      if (strlen($v) > 4000) {
        $v = substr($v, 0, 4000) . '...';
      }
    });

    $payload = json_encode($headers, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    // Truncate overall payload to 8000 characters.
    if (strlen($payload) > 8000) {
      $payload = substr($payload, 0, 8000) . '...';
    }

    // Try using Drupal's logger (preferred). If not available yet, fall back to watchdog() or error_log().
    $message = 'Request headers: @headers';
    $context = ['@headers' => $payload];

    try {
      if (class_exists('\\Drupal') && method_exists('\\Drupal', 'logger')) {
        // Use a dedicated channel name 'request_headers' to make it easy to filter.
        \Drupal::logger('request_headers')->notice($message, $context);
      } elseif (function_exists('watchdog')) {
        // Some environments may not have the WATCHDOG_* constants available
        // (for example, early bootstrap or non-D7 environments). Only pass
        // the severity when the constant is defined; otherwise call the
        // function without it so the default applies.
        if (defined('WATCHDOG_NOTICE')) {
          watchdog('request_headers', $message, $context, WATCHDOG_NOTICE);
        } else {
          watchdog('request_headers', $message, $context);
        }
      } else {
        // As a last resort, write to PHP error log.
        error_log('request_headers: ' . $payload);
      }
    } catch (\Exception $e) {
      // If logging via Drupal services fails because bootstrap isn't complete, fall back to error_log.
      error_log('Failed to log request headers to Drupal logger: ' . $e->getMessage() . ' | headers: ' . $payload);
    }
  } catch (\Throwable $t) {
    // Protect settings load from failing due to any unexpected errors while collecting headers.
    error_log('Error collecting request headers: ' . $t->getMessage());
  }
}
