<?php
/**
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
  return; // Never tag CLI invocations.
}

if (headers_sent()) {
  return; // Something already produced output; don't risk warnings.
}

// Capture pool name from environment if provided via php_admin_value.
$pool = getenv('PHP_FPM_POOL') ?: 'unknown';

// Always include PID for correlation with slowlog / process manager.
header('X-FPM-PHP-PID: ' . getmypid(), false);
header('X-FPM-Pool: ' . $pool, false);

// Optional: expose request time high-resolution (can help spot queueing vs exec time).
$__fpm_tag_hrstart = null;
if (function_exists('hrtime')) {
  // hrtime(true) returns nanoseconds; convert to microseconds for brevity.
  $__fpm_tag_hrstart = hrtime(true);
  header('X-FPM-Start-TS-Us: ' . (int) ($__fpm_tag_hrstart / 1000), false);
}
else {
  // Fallback using microtime(true) in seconds; store as microseconds.
  $__fpm_tag_hrstart = (int) (microtime(true) * 1_000_000) * 1000; // normalize to ns-like units for math below.
}

// Register a shutdown function to append total execution wall time in microseconds.
// Header will only be added if headers are still modifiable at shutdown.
register_shutdown_function(static function () use ($__fpm_tag_hrstart) {
  if (!isset($__fpm_tag_hrstart)) {
    return;
  }
  if (headers_sent()) {
    return; // Output flushed; can't add header.
  }
  if (function_exists('hrtime')) {
    $elapsed_ns = hrtime(true) - $__fpm_tag_hrstart;
  }
  else {
    // $__fpm_tag_hrstart stored as pseudo-ns based on microtime; derive current the same way.
    $elapsed_ns = ((int) (microtime(true) * 1_000_000) * 1000) - $__fpm_tag_hrstart;
  }
  if ($elapsed_ns < 0) {
    return; // Clock skew edge case.
  }
  header('X-FPM-Exec-Us: ' . (int) ($elapsed_ns / 1000), false);
});
