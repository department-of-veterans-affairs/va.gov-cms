<?php

namespace Drupal\va_gov_content_types\Entity;

use Drupal\va_gov_content_types\Traits\EventOutreachTrait;

/**
 * Provides a base class for the Event content type.
 *
 * @codeCoverageIgnore
 */
class Event extends VaNode implements EventInterface {

  use EventOutreachTrait;

  /**
   * {@inheritDoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function eventEntityPresave(EventInterface $event): void {
    $this->addToNationalOutreachCalendar($event);
  }

}
