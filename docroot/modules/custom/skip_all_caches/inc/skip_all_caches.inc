<?php

use Drupal\skip_all_paths\SettingsCacheChecker;
use Symfony\Component\HttpFoundation\Request;

function checkRemoveAllCaches(Request $request, array $settings) : array {
  $settings_cache_checker = SettingsCacheChecker::create($settings);
  if ($settings_cache_checker->shouldSkipAllCache($request)) {
    $settings = $settings_cache_checker->getUpdatedSettings($settings);
  }

  return $settings;
}
