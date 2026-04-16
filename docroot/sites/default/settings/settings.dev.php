<?php

  // Canonical site host used by common settings logic; must match ingress host
  $webhost_on_cli = 'https://eks-dev.cms.va.gov';
  $settings['webhost'] = $webhost_on_cli;

  // Explicitly allow expected Host headers (anchored regex patterns)
  $settings['trusted_host_patterns'] = [
    '^eks-dev\.cms\.va\.gov$',
    '^dev\.cms\.va\.gov$',
    '^test\.dev\.cms\.va\.gov$',
    '^.*\.us-gov-west-1\.elb\.amazonaws\.com$',
    '^va-gov-cms\.ddev\.site$',
    '^localhost$',
    '^10\.199\..*$',   // ALB/ELB health checks by IP
    '^10\.247\..*$',   // ALB/ELB health checks by IP
  ];

  // ———————————————————————————————————————————————
  // Force HTTPS detection via Traefik forwarded headers
  // ———————————————————————————————————————————————
  if (
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_SSL'])   && strtolower($_SERVER['HTTP_X_FORWARDED_SSL'])   === 'on')
  ) {
    $_SERVER['HTTPS']       = 'on';
    $_SERVER['SERVER_PORT'] = 443;
  }

  // Sanitize X-Forwarded-For to contain only valid IPs (remove tokens like "traefik")
  if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $xff_parts = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
    $xff_ips = array_filter($xff_parts, function ($ip) {
      return (bool) filter_var($ip, FILTER_VALIDATE_IP);
    });
    if (!empty($xff_ips)) {
      $_SERVER['HTTP_X_FORWARDED_FOR'] = implode(', ', $xff_ips);
    } else {
      unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    }
  }

  // Increase memory limit for JSONapi requests with many includes
  if (!empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/jsonapi/') !== false) {
    // Count the number of includes to determine resource allocation
    $include_count = 0;
    if (!empty($_GET['include'])) {
      $include_count = substr_count($_GET['include'], ',') + substr_count($_GET['include'], '.');
    }

    // Scale memory and execution time based on complexity
    if ($include_count > 20) {
      @ini_set('memory_limit', '2048M');  // 2GB for very complex requests
      @ini_set('max_execution_time', '900');  // 15 minutes
      error_log("COMPLEX JSONapi request with $include_count include segments: " . $_SERVER['REQUEST_URI']);

      // For extremely complex requests, limit cache tags to avoid Apache's 8KB header buffer limit
      // Apache mod_proxy_fcgi has a hard-coded buffer that cannot be increased via config
      // We must keep the X-Drupal-Cache-Tags header under ~8KB to be safe
      if ($include_count > 15) {
        error_log("COMPLEX JSONapi request with $include_count include segments - enabling aggressive cache tag limiter");

        // Use header_register_callback to intercept headers BEFORE they're sent to Apache
        // This runs earlier than shutdown functions
        if (function_exists('header_register_callback')) {
          header_register_callback(function() {
            $max_header_size = 8192; // 8KB - Apache's actual limit

            $headers = headers_list();
            foreach ($headers as $header) {
              // Check for cache tags header (case-insensitive)
              if (stripos($header, 'X-Drupal-Cache-Tags:') === 0 || stripos($header, 'Cache-Tags:') === 0) {
                $header_parts = explode(':', $header, 2);
                if (count($header_parts) !== 2) continue;

                $header_name = trim($header_parts[0]);
                $header_value = trim($header_parts[1]);
                $header_size = strlen($header);

                // Split tags
                $tags = preg_split('/\s+/', $header_value, -1, PREG_SPLIT_NO_EMPTY);
                $original_count = count($tags);

                // Strip out config: and paragraph: tags - they're not useful for cache invalidation
                // Config changes are infrequent and usually require full cache clears anyway
                // Paragraphs are embedded in nodes, so node: tags are sufficient
                $filtered_tags = [];
                $config_tags_removed = 0;
                $paragraph_tags_removed = 0;

                foreach ($tags as $tag) {
                  if (strpos($tag, 'config:') === 0) {
                    $config_tags_removed++;
                    continue; // Skip config tags
                  }
                  if (strpos($tag, 'paragraph:') === 0) {
                    $paragraph_tags_removed++;
                    continue; // Skip paragraph tags
                  }
                  $filtered_tags[] = $tag;
                }

                $new_value = implode(' ', $filtered_tags);
                $new_size = strlen($header_name . ': ' . $new_value);

                // If still too large after removing config and paragraph tags, limit further
                if ($new_size > $max_header_size) {
                  error_log("Cache tags still too large after removing config/paragraph tags: {$new_size} bytes. Further limiting.");

                  // Prioritize node and taxonomy tags over other types
                  $priority_tags = [];
                  $other_tags = [];

                  foreach ($filtered_tags as $tag) {
                    if (strpos($tag, 'node:') === 0 || strpos($tag, 'taxonomy_term:') === 0) {
                      $priority_tags[] = $tag;
                    } else {
                      $other_tags[] = $tag;
                    }
                  }

                  // Build limited list that fits in the header
                  $limited_tags = $priority_tags;
                  $new_value = implode(' ', $limited_tags);

                  // Add other tags until we hit the size limit
                  foreach ($other_tags as $tag) {
                    $test_value = $new_value . ' ' . $tag;
                    if (strlen($header_name . ': ' . $test_value) > $max_header_size - 100) {
                      break; // Stop adding tags
                    }
                    $limited_tags[] = $tag;
                    $new_value = $test_value;
                  }

                  $filtered_tags = $limited_tags;
                  $new_value = implode(' ', $filtered_tags);
                  $new_size = strlen($header_name . ': ' . $new_value);
                }

                // Replace the header if we removed tags or need to limit
                if ($config_tags_removed > 0 || $paragraph_tags_removed > 0 || count($filtered_tags) < $original_count) {
                  header_remove($header_name);
                  header($header_name . ': ' . $new_value);
                  header('X-Cache-Tags-Modified: true');
                  header('X-Cache-Tags-Original-Count: ' . $original_count);
                  header('X-Cache-Tags-Config-Removed: ' . $config_tags_removed);
                  header('X-Cache-Tags-Paragraph-Removed: ' . $paragraph_tags_removed);
                  header('X-Cache-Tags-Final-Count: ' . count($filtered_tags));

                  error_log("Modified cache tags: removed {$config_tags_removed} config + {$paragraph_tags_removed} paragraph tags, {$original_count} -> " . count($filtered_tags) . " total tags. Final size: {$new_size} bytes");
                }

                break;
              }
            }
          });
        }
      }
    } else {
      @ini_set('memory_limit', '2048M');
      @ini_set('max_execution_time', '900');
    }

    @ini_set('display_errors', '0');  // Don't display errors in response (corrupts JSON)
    @ini_set('display_startup_errors', '0');
    @ini_set('log_errors', '1');  // Log to stderr
    @ini_set('error_log', '/proc/1/fd/2');
    error_reporting(E_ALL);

    // Log the request for debugging
    error_log('JSONapi request: ' . $_SERVER['REQUEST_URI']);

    // Register shutdown function to catch fatal errors
    register_shutdown_function(function() {
      $error = error_get_last();
      if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        error_log('FATAL ERROR in JSONapi: ' . json_encode($error));
        error_log('Memory usage at error: ' . memory_get_peak_usage(true) . ' bytes');
      }
    });
  }

  // Increase entity query access check limit (default is 50)
  $settings['entity_query_access_check_limit'] = 500;

  // Increase entity reference selection limit for complex queries
  $settings['entity_autocomplete_match_limit'] = 500;

  // Enable aggressive entity caching for JSONapi to speed up cold cache rebuilds
  // This caches the loaded entities in memory during the request
  $settings['entity_update_batch_size'] = 50;

  // Enable render caching for JSONapi responses
  $settings['cache']['bins']['jsonapi_normalizations'] = 'cache.backend.memcache';
  $settings['cache']['bins']['jsonapi_resource_types'] = 'cache.backend.memcache';
  $settings['cache']['bins']['entity'] = 'cache.backend.memcache';

  // Database configuration for complex queries
  $databases['default']['default']['init_commands'] = [
    'isolation_level' => 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED',
    'lock_wait_timeout' => 'SET SESSION lock_wait_timeout = 60',
    'tmp_table_size' => 'SET SESSION tmp_table_size = 536870912', // 512MB (increased from 256MB)
    'max_heap_table_size' => 'SET SESSION max_heap_table_size = 536870912', // 512MB (increased from 256MB)
    'join_buffer_size' => 'SET SESSION join_buffer_size = 33554432', // 32MB (increased from 16MB)
    'sort_buffer_size' => 'SET SESSION sort_buffer_size = 33554432', // 32MB (increased from 16MB)
    'read_buffer_size' => 'SET SESSION read_buffer_size = 8388608', // 8MB for sequential scans
    'read_rnd_buffer_size' => 'SET SESSION read_rnd_buffer_size = 16777216', // 16MB for sorted reads
  ];

  $settings['reverse_proxy'] = TRUE;
  $settings['reverse_proxy_addresses'] = [
    '10.199.0.0/16',
    '10.247.0.0/16',
    '127.0.0.1',
    '172.18.0.0/16',
    '172.19.0.0/16',
    '172.20.0.0/16',
    '172.21.0.0/16',
    '172.22.0.0/16',
    '172.23.0.0/16',
    '172.24.0.0/16',
    '172.25.0.0/16',
    '172.26.0.0/16',
    '172.27.0.0/16',
    '172.28.0.0/16',
    '172.29.0.0/16',
    '172.30.0.0/16',
    '172.31.0.0/16',
    '192.168.0.0/16',
  ];

  // Enable all X-Forwarded headers
  $settings['reverse_proxy_trusted_headers'] =
    \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_FOR |
    \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_HOST |
    \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PORT |
    \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PROTO;

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

  $config['system.performance']['cache']['page']['use_internal'] = FALSE;
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

  $settings['va_gov_frontend_url'] = 'https://dev.va.gov';
  $settings['va_gov_frontend_build_type'] = 'brd';
  $settings['github_actions_deploy_env'] = 'dev';

  // Public asset S3 location
  $public_asset_s3_base_url = 'https://dsva-vagov-staging-cms-files.s3.us-gov-west-1.amazonaws.com';

  // Entra ID settings
  $settings['microsoft_entra_id_client_id'] = getenv('MICROSOFT_ENTRA_ID_CLIENT_ID');
  $settings['microsoft_entra_id_client_secret'] = getenv('MICROSOFT_ENTRA_ID_CLIENT_SECRET');
  $settings['microsoft_entra_id_tenant_id'] = getenv('MICROSOFT_ENTRA_ID_TENANT_ID');

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
