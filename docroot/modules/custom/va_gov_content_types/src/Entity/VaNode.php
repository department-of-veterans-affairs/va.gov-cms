<?php

namespace Drupal\va_gov_content_types\Entity;

use Drupal\node\Entity\Node;
use Drupal\va_gov_content_types\Traits\ContentModerationTrait;
use Drupal\va_gov_content_types\Traits\ContentModerationTransitionsTrait;
use Drupal\va_gov_content_types\Traits\ContentReleaseTriggerTrait;
use Drupal\va_gov_content_types\Traits\DidChangeOperatingStatusTrait;
use Drupal\va_gov_content_types\Traits\GetOriginalTrait;
use Drupal\va_gov_content_types\Traits\IsFacilityTrait;

/**
 * Provides an abstract base class for all project content types.
 *
 * @codeCoverageIgnore
 */
class VaNode extends Node implements VaNodeInterface {

  use ContentModerationTrait;
  use ContentModerationTransitionsTrait;
  use ContentReleaseTriggerTrait;
  use DidChangeOperatingStatusTrait;
  use GetOriginalTrait;
  use IsFacilityTrait;

}
