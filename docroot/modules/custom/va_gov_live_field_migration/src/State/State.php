<?php

namespace Drupal\va_gov_live_field_migration\State;

use Drupal\Core\State\StateInterface as CoreStateInterface;
use Drupal\va_gov_live_field_migration\Exception\StatusNotFoundException;
use Drupal\va_gov_live_field_migration\Migration\Status\Key\Key;
use Drupal\va_gov_live_field_migration\Migration\Status\Status;
use Drupal\va_gov_live_field_migration\Migration\Status\StatusInterface;

/**
 * The migration state management service.
 */
class State implements StateInterface {

  /**
   * The Core state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected CoreStateInterface $coreState;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\State\StateInterface $coreState
   *   The Core state service.
   */
  public function __construct(CoreStateInterface $coreState) {
    $this->coreState = $coreState;
  }

  /**
   * {@inheritDoc}
   */
  public function getStatus(string $migrationId, string $entityType, string $fieldName): StatusInterface {
    $key = Key::getKey($migrationId, $entityType, $fieldName);
    $status = $this->coreState->get($key);
    if ($status === NULL) {
      throw new StatusNotFoundException("Status not found for key: $key");
    }
    return Status::fromJson($status);
  }

  /**
   * {@inheritDoc}
   */
  public function setStatus(StatusInterface $status): void {
    $this->coreState->set($status->getKey(), $status->toJson());
  }

  /**
   * {@inheritDoc}
   */
  public function deleteStatus(string $migrationId, string $entityType, string $fieldName): void {
    $key = Key::getKey($migrationId, $entityType, $fieldName);
    $this->coreState->delete($key);
  }

}
