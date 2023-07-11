<?php

namespace Drupal\va_gov_content_types\Traits;

use Drupal\va_gov_content_types\Exception\NonFacilityException;
use Drupal\va_gov_content_types\Interfaces\DidChangeOperatingStatusInterface;

/**
 * Provides a trait for determining whether the facility changed status.
 */
trait DidChangeOperatingStatusTrait {

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
    if (!$this->hasField(static::STATUS_FIELD)) {
      return FALSE;
    }
    return $this->didChangeField(DidChangeOperatingStatusInterface::STATUS_FIELD) || $this->didChangeField(DidChangeOperatingStatusInterface::STATUS_INFO_FIELD);
  }

}
