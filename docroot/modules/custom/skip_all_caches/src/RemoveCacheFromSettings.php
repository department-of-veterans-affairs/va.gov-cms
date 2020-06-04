<?php

namespace Drupal\skip_all_paths;


use Drupal\Core\Cache\DatabaseCacheTagsChecksum;
use Drupal\Core\Cache\NullBackend;
use Drupal\Core\Database\Connection;

class RemoveCacheFromSettings {

  /**
   * Update settings array to remove all caching.
   *
   * @param array $settings
   *
   * @return array
   */
  public function updateSettingsArray(array $settings) : array {
    $settings['cache']['default'] = 'cache.backend.null';
    $settings['class_loader_auto_detect'] = FALSE;
    // Override bootstrap cache container from DrupalKernal::defaultBootstrapContainerDefinition
    $settings['bootstrap_container_definition'] = [
      'parameters' => [
        'cache_default_bin_backends' => 'cache.backend.null'
      ],
      'services' => [
        'database' => [
          'class' => Connection::class,
          'factory' => 'Drupal\Core\Database\Database::getConnection',
          'arguments' => ['default'],
        ],
        'cache.container' => [
          'class' => NullBackend::class,
          'arguments' => ['container'],
        ],
        'cache_tags_provider.container' => [
          'class' => DatabaseCacheTagsChecksum::class,
          'arguments' => ['@database'],
        ],
      ],
    ];

    foreach ($this->getCacheBins() as $bin) {
      $settings['cache']['bins'][$bin] = 'cache.backend.null';
    }

    return $settings;
  }

  /**
   * Get all of the cache bins.
   *
   * This list is pulled from https://www.drupal.org/node/2598914.
   * This list can be regenerated from ./scripts/generate-cache-bins.sh
   */
  protected function getCacheBins() : array {
    return [
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
  }
}
