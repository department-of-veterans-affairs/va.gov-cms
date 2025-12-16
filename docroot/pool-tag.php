<?php

/**
 * @file
 * Auto-prepended script to tag outgoing responses with FPM pool info.
 *
 * This runs before Drupal's bootstrap on every dynamic PHP request (due to
 * auto_prepend_file). It's deliberately tiny and defensive:
 *  - Skips CLI (drush, cron, etc.).
 *  - Avoids emitting headers if already sent (edge early output cases).
 *  - Only adds headers for normal web requests.
 *
 * To have an accurate pool name, define in each php-fpm pool config:
 *   php_admin_value[PHP_FPM_POOL] = www
 *   php_admin_value[PHP_FPM_POOL] = heavy
 * If not defined, the header falls back to 'unknown'.
 */

if (PHP_SAPI === 'cli') {
  // Never tag CLI invocations.
  return;
}

if (headers_sent()) {
  // Something already produced output; don't risk warnings.
  return;
}

// Capture pool name from environment if provided via php_admin_value.
$pool = getenv('PHP_FPM_POOL') ?: 'unknown';

// Always include PID for correlation with slowlog / process manager.
header('X-FPM-PHP-PID: ' . getmypid(), FALSE);
header('X-FPM-Pool: ' . $pool, FALSE);

// Optional: expose request time high-resolution
// (to spot queueing vs exec time).
$__fpm_tag_hrstart = NULL;
if (function_exists('hrtime')) {
  // hrtime(true) returns nanoseconds; convert to microseconds for brevity.
  $__fpm_tag_hrstart = hrtime(TRUE);
  header('X-FPM-Start-TS-Us: ' . (int) ($__fpm_tag_hrstart / 1000), FALSE);
}
else {
  // Fallback using microtime(true) in seconds; store as microseconds.
  // normalize to ns-like units for math below.
  $__fpm_tag_hrstart = (int) (microtime(TRUE) * 1_000_000) * 1000;
}

// Register a shutdown function to append total execution wall time
// in microseconds.
// Header will only be added if headers are still modifiable at shutdown.
register_shutdown_function(static function () use ($__fpm_tag_hrstart) {
  if (!isset($__fpm_tag_hrstart)) {
    return;
  }
  if (headers_sent()) {
    // Output flushed; can't add header.
    return;
  }
  if (function_exists('hrtime')) {
    $elapsed_ns = hrtime(TRUE) - $__fpm_tag_hrstart;
  }
  else {
    // $__fpm_tag_hrstart stored as pseudo-ns based on microtime;
    // derive current the same way.
    $elapsed_ns = ((int) (microtime(TRUE) * 1_000_000) * 1000) - $__fpm_tag_hrstart;
  }
  if ($elapsed_ns < 0) {
    // Clock skew edge case.
    return;
  }
  header('X-FPM-Exec-Us: ' . (int) ($elapsed_ns / 1000), FALSE);
});
