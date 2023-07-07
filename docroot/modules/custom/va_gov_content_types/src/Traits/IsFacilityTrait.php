<?php

namespace Drupal\va_gov_content_types\Traits;

use Drupal\va_gov_content_types\Interfaces\IsFacilityInterface;

/**
 * Allows easy determination of facility content types.
 *
 * This is currently used for facilities that are rendered on the front end.
 */
trait IsFacilityTrait {

  /**
   * Is this content type a facility content type?
   *
   * @return bool
   *   TRUE if this content type is a facility content type, FALSE otherwise.
   */
  public function isFacility(): bool {
    return in_array($this->getType(), IsFacilityInterface::FACILITY_CONTENT_TYPES);
  }

}
