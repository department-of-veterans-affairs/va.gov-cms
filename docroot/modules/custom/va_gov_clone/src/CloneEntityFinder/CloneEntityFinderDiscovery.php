<?php

namespace Drupal\va_gov_clone\CloneEntityFinder;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Clone handler plugin manager.
 */
class CloneEntityFinderDiscovery extends DefaultPluginManager {

  /**
   * Constructs a new CloneHandlerManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/VAGov/CloneHandler', $namespaces, $module_handler,
      'Drupal\va_gov_clone\CloneEntityFinder\CloneEntityFinderInterface', 'Drupal\va_gov_clone\CloneEntityFinder\Annotation\CloneEntityFinder');

    $this->alterInfo('va_gov_clone_clone_entity_finder_info');
    $this->setCacheBackend($cache_backend, 'va_gov_clone_clone_entity_finder_info');
  }

}
