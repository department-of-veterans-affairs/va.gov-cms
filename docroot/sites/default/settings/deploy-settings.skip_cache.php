<?php

use Symfony\Component\HttpFoundation\Request;

$settings['skip_all_caches_enabled'] = TRUE;
$settings['skip_all_caches_for_paths'] = [
  'ajax/site_alert',
];
$settings['skip_all_caches_checkers'] = [
  'skip_all_caches_cli_checker',
  'Drupal\skip_all_caches\Checker\SkipCacheForPaths',
];

$settings['skip_all_cache_bins'] = [
  'bootstrap',
  'cache_rebuild_command',
  'cache_tags.invalidator',
  'config',
  'data',
  'default',
  'discovery',
  'discovery_migration',
  'dynamic_page_cache',
  'entity',
  'graphql.definitions',
  'graphql.results',
  'jsonapi_memory',
  'jsonapi_normalizations',
  'jsonapi_resource_types',
  'libraries',
  'menu',
  'migrate',
  'page',
  'render',
  'rest',
  'static',
  'tome_static',
  'toolbar',
];

include $app_root . '/' . $site_path . '/settings/skip_all_caches.inc';
include_once $app_root . '/modules/contrib/skip_all_caches/inc/skip_all_caches.inc';

// Create a request object so we have something to pass Fast404.
$request = Request::createFromGlobals();
$settings = checkRemoveAllCaches($request, $settings);
