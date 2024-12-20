<?php

namespace Drupal\va_gov_live_field_migration\FieldProvider\Plugin;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\va_gov_live_field_migration\Annotation\FieldProvider;
use Drupal\va_gov_live_field_migration\Exception\FieldProviderNotFoundException;

/**
 * Manages the FieldProvider plugins.
 */
class FieldProviderPluginManager extends DefaultPluginManager implements FieldProviderPluginManagerInterface {

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct(
      'Plugin/FieldProvider',
      $namespaces,
      $module_handler,
      FieldProviderPluginInterface::class,
      FieldProvider::class
    );
    $this->alterInfo('va_gov_live_field_migration_field_provider_info');
    $this->setCacheBackend($cache_backend, 'va_gov_live_field_migration_field_provider');
  }

  /**
   * {@inheritDoc}
   */
  public function getFieldProvider(string $id) : FieldProviderPluginInterface {
    try {
      return $this->createInstance($id);
    }
    catch (PluginException) {
      throw new FieldProviderNotFoundException("Unknown field provider: $id");
    }
  }

}
