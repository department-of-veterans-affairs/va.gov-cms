<?php

namespace Drupal\va_gov_content_types\Entity;

use Drupal\node\NodeInterface;
use Drupal\va_gov_content_types\Interfaces\GetTypeInterface;
use Drupal\va_gov_content_types\Interfaces\IsFacilityInterface;

/**
 * Provides an interface for all project content types.
 */
interface VaNodeInterface extends NodeInterface, GetTypeInterface, IsFacilityInterface {

}
