<?php

namespace Drupal\va_gov_live_field_migration\State;

use Drupal\va_gov_live_field_migration\Migration\Status\StatusInterface;

/**
 * An interface for the state management service.
 */
interface StateInterface {

  /**
   * Get information about the specified migration.
   *
   * @param string $migrationId
   *   The migration ID.
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @return \Drupal\va_gov_live_field_migration\Migration\Status\StatusInterface
   *   An object containing information about the migration.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\StatusNotFoundException
   *   If the status cannot be found.
   */
  public function getStatus(string $migrationId, string $entityType, string $fieldName): StatusInterface;

  /**
   * Create a status object for the specified migration.
   *
   * @param string $migrationId
   *   The migration ID.
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   *
   * @return \Drupal\va_gov_live_field_migration\Migration\Status\StatusInterface
   *   A new object containing information about the migration.
   */
  public function createStatus(string $migrationId, string $entityType, string $fieldName): StatusInterface;

  /**
   * Set information about the specified migration.
   *
   * @param \Drupal\va_gov_live_field_migration\Migration\Status\StatusInterface $status
   *   An object containing information about the migration.
   */
  public function setStatus(StatusInterface $status): void;

  /**
   * Deletes information about the specified migration.
   *
   * @param string $migrationId
   *   The migration ID.
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   */
  public function deleteStatus(string $migrationId, string $entityType, string $fieldName): void;

}
