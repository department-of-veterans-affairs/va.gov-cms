<?php

namespace Drupal\va_gov_live_field_migration\FieldProvider\Plugin;

/**
 * An interface for the field provider plugin manager.
 */
interface FieldProviderPluginManagerInterface {

  /**
   * Get the field provider plugin.
   *
   * @param string $id
   *   The plugin ID.
   *
   * @return \Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginInterface
   *   The field provider plugin.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\FieldProviderNotFoundException
   *   If the field provider cannot be found.
   */
  public function getFieldProvider(string $id) : FieldProviderPluginInterface;

}
