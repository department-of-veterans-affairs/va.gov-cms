<?php

namespace Drupal\va_gov_live_field_migration\Migration\Status;

use Drupal\va_gov_live_field_migration\Exception\StatusDeserializationException;
use Drupal\va_gov_live_field_migration\Migration\Status\Key\Key;

/**
 * A class for migration status.
 */
class Status implements StatusInterface, \JsonSerializable {

  /**
   * The entity type.
   *
   * @var string
   */
  protected $entityType;

  /**
   * The field name.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * The migration ID.
   *
   * @var string
   */
  protected $migrationId;

  /**
   * The status.
   *
   * @var string
   */
  protected $status;

  /**
   * Constructor.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $fieldName
   *   The field name.
   * @param string $migrationId
   *   The migration ID.
   * @param string $status
   *   The status.
   */
  public function __construct(
    string $entityType,
    string $fieldName,
    string $migrationId,
    string $status = NULL
  ) {
    $this->entityType = $entityType;
    $this->fieldName = $fieldName;
    $this->migrationId = $migrationId;
    $this->status = $status ?? static::DEFAULT_STATUS;
  }

  /**
   * {@inheritDoc}
   */
  public function toJson(): string {
    return json_encode($this);
  }

  /**
   * Validate a data object for deserialization.
   *
   * @param array $data
   *   The data object.
   *
   * @throws \Drupal\va_gov_live_field_migration\Exception\StatusDeserializationException
   *   If the data object is invalid.
   */
  protected static function validateData(array $data) {
    if (empty($data['entityType'])) {
      throw new StatusDeserializationException('Missing entity type.');
    }
    if (empty($data['fieldName'])) {
      throw new StatusDeserializationException('Missing field name.');
    }
    if (empty($data['migrationId'])) {
      throw new StatusDeserializationException('Missing migration ID.');
    }
  }

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
  public static function fromJson($json): StatusInterface {
    $data = json_decode($json, JSON_OBJECT_AS_ARRAY);
    if (empty($data)) {
      throw new StatusDeserializationException('Unable to deserialize status.');
    }
    self::validateData($data);
    $result = new Status(
      $data['entityType'],
      $data['fieldName'],
      $data['migrationId'],
      $data['status'] ?? static::DEFAULT_STATUS
    );
    return $result;
  }

  /**
   * {@inheritDoc}
   */
  public function getKey(): string {
    return Key::getKey($this->migrationId, $this->entityType, $this->fieldName);
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityType(): string {
    return $this->entityType;
  }

  /**
   * {@inheritDoc}
   */
  public function getFieldName(): string {
    return $this->fieldName;
  }

  /**
   * {@inheritDoc}
   */
  public function getMigrationId(): string {
    return $this->migrationId;
  }

  /**
   * {@inheritDoc}
   */
  public function getStatus(): string {
    return $this->status;
  }

  /**
   * {@inheritDoc}
   */
  public function setStatus(string $status): void {
    $this->status = $status;
  }

  /**
   * {@inheritDoc}
   */
  public function jsonSerialize(): mixed {
    return [
      'entityType' => $this->getEntityType(),
      'fieldName' => $this->getFieldName(),
      'migrationId' => $this->getMigrationId(),
      'status' => $this->getStatus(),
    ];
  }

}
