<?php

namespace Drupal\va_gov_live_field_migration\Migration\Plugin;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\va_gov_live_field_migration\Annotation\Migration;
use Drupal\va_gov_live_field_migration\Exception\MigrationNotFoundException;

/**
 * Manages the Migration plugins.
 */
class MigrationPluginManager extends DefaultPluginManager implements MigrationPluginManagerInterface {

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
      'Plugin/Migration',
      $namespaces,
      $module_handler,
      MigrationPluginInterface::class,
      Migration::class
    );
    $this->alterInfo('va_gov_live_field_migration_migration_info');
    $this->setCacheBackend($cache_backend, 'va_gov_live_field_migration_migration');
  }

  /**
   * {@inheritDoc}
   */
  public function getMigration(string $id) : MigrationPluginInterface {
    try {
      return $this->createInstance($id);
    }
    catch (PluginException) {
      throw new MigrationNotFoundException("Unknown migration: $id");
    }
  }

}
