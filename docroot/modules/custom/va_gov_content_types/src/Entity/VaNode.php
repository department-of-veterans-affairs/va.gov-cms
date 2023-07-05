<?php

namespace Drupal\va_gov_content_types\Entity;

use Drupal\node\Entity\Node;
use Drupal\va_gov_content_types\Traits\IsFacilityTrait;

/**
 * Provides an abstract base class for all project content types.
 */
class VaNode extends Node implements VaNodeInterface {

  use IsFacilityTrait;

}
