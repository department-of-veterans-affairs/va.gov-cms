<?php

namespace Drupal\va_gov_build_trigger\Environment;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\va_gov_build_trigger\Environment\Annotation\Environment;

/**
 * Manages the Environment Plugins.
 */
class EnvironmentManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/VAGov/Environment', $namespaces, $module_handler, EnvironmentInterface::class, Environment::class);

    $this->alterInfo('va_gov_build_environment');
    $this->setCacheBackend($cache_backend, 'va_gov_build_environments');
  }

}
