<?php

namespace Drupal\va_gov_live_field_migration\Plugin\FieldProvider;

use Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginBase;

/**
 * Test field provider that returns an empty list.
 *
 * @FieldProvider(
 *   id = "test_empty_list",
 *   label = @Translation("Empty List Test")
 * )
 */
class TestEmptyList extends FieldProviderPluginBase {

  /**
   * {@inheritDoc}
   */
  public function getFields(string $entityType, string $bundle = NULL) : array {
    return [];
  }

}
