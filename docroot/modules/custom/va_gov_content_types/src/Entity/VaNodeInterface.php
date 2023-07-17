<?php

namespace Drupal\va_gov_content_types\Entity;

use Drupal\node\NodeInterface;
use Drupal\va_gov_content_types\Interfaces\ContentModerationInterface;
use Drupal\va_gov_content_types\Interfaces\ContentModerationTransitionsInterface;
use Drupal\va_gov_content_types\Interfaces\ContentReleaseTriggerInterface;
use Drupal\va_gov_content_types\Interfaces\DidChangeOperatingStatusInterface;
use Drupal\va_gov_content_types\Interfaces\GetOriginalInterface;
use Drupal\va_gov_content_types\Interfaces\IsFacilityInterface;

/**
 * Provides an interface for all project content types.
 */
interface VaNodeInterface extends
  NodeInterface,
  ContentModerationInterface,
  ContentModerationTransitionsInterface,
  ContentReleaseTriggerInterface,
  DidChangeOperatingStatusInterface,
  GetOriginalInterface,
  IsFacilityInterface {

}
