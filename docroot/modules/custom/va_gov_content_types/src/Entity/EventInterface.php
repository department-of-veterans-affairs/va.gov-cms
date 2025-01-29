<?php

namespace Drupal\va_gov_content_types\Entity;

use Drupal\va_gov_content_types\Interfaces\EventOutreachInterface;

/**
 * Provides an interface for Event nodes.
 */
interface EventInterface extends VaNodeInterface, EventOutreachInterface {

  /**
   * Pre-save an Event content type.
   *
   * @param \Drupal\va_gov_content_types\Entity\EventInterface $event
   *   The event node.
   */
  public function eventEntityPresave(self $event) :void;

}
