<?php

namespace Drupal\va_gov_content_types\Interfaces;

/**
 * Provides an interface for determining whether a node is a facility.
 */
interface IsFacilityInterface {

  const FACILITY_CONTENT_TYPES = [
    'health_care_local_facility',
    // 'nca_facility',  // Not rendered on the FE yet.  Add it when it is.
    'vba_facility',
    'vet_center_cap',
    'vet_center_outstation',
    'vet_center',
  ];

  /**
   * Is this content type a facility content type?
   *
   * @return bool
   *   TRUE if this content type is a facility content type, FALSE otherwise.
   */
  public function isFacility(): bool;

}
