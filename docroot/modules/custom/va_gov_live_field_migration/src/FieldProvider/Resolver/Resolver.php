<?php

namespace Drupal\va_gov_live_field_migration\FieldProvider\Resolver;

use Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginInterface;
use Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginManagerInterface;

/**
 * Resolves a context to a field provider plugin.
 */
class Resolver implements ResolverInterface {

  /**
   * The plugin manager.
   *
   * @var \Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginManagerInterface
   */
  protected FieldProviderPluginManagerInterface $pluginManager;

  /**
   * {@inheritDoc}
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    FieldProviderPluginManagerInterface $pluginManager
  ) {
    $this->pluginManager = $pluginManager;
  }

  /**
   * {@inheritDoc}
   */
  public function getFieldProvider(string $id) : FieldProviderPluginInterface {
    return $this->pluginManager->getFieldProvider($id);
  }

}
