<?php

use Symfony\Component\HttpFoundation\Request;

$settings['skip_all_caches_enabled'] = TRUE;
$settings['skip_all_caches_for_paths'] = [
  'ajax/site_alert',
];

include $app_root . '/' . $site_path . '/settings/skip_all_caches.inc';

// Create a request object so we have something to pass Fast404.
$request = Request::createFromGlobals();
$settings = checkRemoveAllCaches($request, $settings);
