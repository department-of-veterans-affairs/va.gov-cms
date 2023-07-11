<?php

namespace Drupal\va_gov_content_types\Interfaces;

/**
 * Provides an interface for determining whether the facility changed status.
 */
interface DidChangeOperatingStatusInterface {

  const STATUS_FIELD = 'field_operating_status_facility';
  const STATUS_INFO_FIELD = 'field_operating_status_more_info';

  /**
   * Did this facility change operating status?
   *
   * @return bool
   *   TRUE if status changed, FALSE otherwise.
   *
   * @throws \Drupal\va_gov_content_types\Exception\NonFacilityException
   *   Thrown when the node is not a facility.
   */
  public function didChangeOperatingStatus(): bool;

}
