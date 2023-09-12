<?php

namespace Drupal\va_gov_live_field_migration\Migration\Status;

/**
 * An interface for migration status.
 */
interface StatusInterface {

  const DEFAULT_STATUS = 'not_started';

  /**
   * Gets the status key.
   *
   * @return string
   *   The status key.
   */
  public function getKey(): string;

  /**
   * Gets the entity type.
   *
   * @return string
   *   The entity type.
   */
  public function getEntityType(): string;

  /**
   * Gets the field name.
   *
   * @return string
   *   The field name.
   */
  public function getFieldName(): string;

  /**
   * Gets the migration ID.
   *
   * This is the migration plugin ID, not a unique identifier.
   *
   * @return string
   *   The migration ID.
   */
  public function getMigrationId(): string;

  /**
   * Gets the status.
   *
   * @return string
   *   The status.
   */
  public function getStatus(): string;

  /**
   * Sets the status.
   *
   * @param string $status
   *   The status.
   */
  public function setStatus(string $status): void;

  /**
   * Serializes the object to a JSON string.
   *
   * @return string
   *   The JSON string.
   */
  public function toJson(): string;

  /**
   * Deserializes the object from a JSON string.
   *
   * @param string $json
   *   The JSON string.
   *
   * @return \Drupal\va_gov_live_field_migration\Migration\Status\StatusInterface
   *   The status object.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\StatusDeserializationException
   *   If the JSON string cannot be deserialized.
   */
  public static function fromJson($json): StatusInterface;

}
