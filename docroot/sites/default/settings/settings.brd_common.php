<?php

// @codingStandardsIgnoreFile

// Memcache-specific settings
if (extension_loaded('memcache')) {
  $memcache_nodes = getenv('CMS_MEMCACHE_NODES');
  if (!empty($memcache_nodes)) {
    $memcache_servers = explode(',', $memcache_nodes);
    $memcache_servers = array_map($memcache_servers, function ($memcache_server) {
      return trim($memcache_server) . ':11211';
    });
    $settings['memcache']['servers'] = [];
    foreach ($memcache_servers as $memcache_server) {
      $settings['memcache']['servers'][$memcache_server] = 'default';
    }
    $settings['cache']['default'] = 'cache.backend.memcache';
    $settings['memcache']['bins'] = [
      'default' => 'default',
    ];
  }
}
