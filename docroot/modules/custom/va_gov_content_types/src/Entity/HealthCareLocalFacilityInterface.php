<?php

namespace Drupal\va_gov_content_types\Entity;

use Drupal\va_gov_content_types\Interfaces\DidChangeOperatingStatusInterface;

/**
 * Provides an interface for health_care_local_facility nodes.
 */
interface HealthCareLocalFacilityInterface extends
  VaNodeInterface,
  DidChangeOperatingStatusInterface {

}
