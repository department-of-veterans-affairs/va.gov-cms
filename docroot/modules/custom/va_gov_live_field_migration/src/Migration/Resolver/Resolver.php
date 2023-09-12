<?php

namespace Drupal\va_gov_live_field_migration\Migration\Resolver;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\va_gov_live_field_migration\Exception\MigrationNotFoundException;
use Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginInterface;
use Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginManagerInterface;

/**
 * Resolves an entity type and field name to a migration plugin.
 */
class Resolver implements ResolverInterface {

  /**
   * The plugin manager.
   *
   * @var \Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginManagerInterface
   */
  protected MigrationPluginManagerInterface $pluginManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected EntityFieldManagerInterface $entityFieldManager;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    MigrationPluginManagerInterface $pluginManager,
    EntityFieldManagerInterface $entityFieldManager
  ) {
    $this->pluginManager = $pluginManager;
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * Calculate the migration plugin ID for a given field.
   *
   * We might someday use a more complex algorithm for this, but right now we:
   *
   * - Retrieve the field storage for the specified field (or assert).
   * - If the field is of type 'string', then we use 'string_to_string_long'.
   * - If the field is of type 'text_long', then we use 'text_to_string_long'.
   * - Otherwise, we throw an exception.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @return string
   *   The migration plugin ID.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\MigrationNotFoundException
   *   If a suitable strategy cannot be found.
   */
  public function getMigrationId(string $entityType, string $fieldName): string {
    $fieldStorage = $this->entityFieldManager->getFieldStorageDefinitions($entityType)[$fieldName];
    if ($fieldStorage->getType() === 'string') {
      return 'string_to_string_long';
    }
    elseif ($fieldStorage->getType() === 'text_long') {
      return 'text_to_string_long';
    }
    throw new MigrationNotFoundException(sprintf('No migration found for field %s on entity type %s.', $fieldName, $entityType));
  }

  /**
   * {@inheritDoc}
   */
  public function getMigration(string $entityType, string $fieldName) : MigrationPluginInterface {
    $id = $this->getMigrationId($entityType, $fieldName);
    return $this->pluginManager->getMigration($id);
  }

}
