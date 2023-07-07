<?php

namespace Drupal\va_gov_content_types\Entity;

use Drupal\va_gov_content_types\Traits\DidChangeOperatingStatusTrait;

/**
 * Provides a class for the health_care_local_facility content type.
 */
class HealthCareLocalFacility extends VaNode implements HealthCareLocalFacilityInterface {

  use DidChangeOperatingStatusTrait;

}
