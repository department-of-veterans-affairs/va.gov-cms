<?php

namespace Drupal\va_gov_content_types\Traits;

use Drupal\va_gov_content_types\Exception\NonFacilityException;
use Drupal\va_gov_content_types\Interfaces\DidChangeOperatingStatusInterface;

/**
 * Provides a trait for determining whether the facility changed status.
 */
trait DidChangeOperatingStatusTrait {

  /**
   * {@inheritDoc}
   */
  abstract public function hasField($fieldName);

  /**
   * {@inheritDoc}
   */
  abstract public function isFacility(): bool;

  /**
   * {@inheritDoc}
   */
  abstract public function didChangeField(string $fieldName): bool;

  /**
   * {@inheritDoc}
   */
  abstract public function hasOriginal(): bool;

  /**
   * Did this facility change operating status?
   *
   * @return bool
   *   TRUE if status changed, FALSE otherwise.
   *
   * @throws \Drupal\va_gov_content_types\Exception\NonFacilityException
   *   Thrown when the node is not a facility.
   */
  public function didChangeOperatingStatus(): bool {
    if (!$this->isFacility()) {
      throw new NonFacilityException('This node is not a facility.');
    }
    if (!$this->hasField(DidChangeOperatingStatusInterface::STATUS_FIELD)) {
      return FALSE;
    }
    if (!$this->hasOriginal()) {
      return FALSE;
    }
    return $this->didChangeField(DidChangeOperatingStatusInterface::STATUS_FIELD) || $this->didChangeField(DidChangeOperatingStatusInterface::STATUS_INFO_FIELD);
  }

}
