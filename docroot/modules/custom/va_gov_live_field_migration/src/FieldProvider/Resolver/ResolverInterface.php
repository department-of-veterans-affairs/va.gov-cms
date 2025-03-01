<?php

namespace Drupal\va_gov_live_field_migration\FieldProvider\Resolver;

use Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginInterface;

/**
 * An interface for the Resolver service.
 */
interface ResolverInterface {

  /**
   * Get the field provider plugin.
   *
   * @return \Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginInterface
   *   The field provider plugin.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\FieldProviderNotFoundException
   *   If the field provider cannot be found.
   */
  public function getFieldProvider(string $id) : FieldProviderPluginInterface;

}
