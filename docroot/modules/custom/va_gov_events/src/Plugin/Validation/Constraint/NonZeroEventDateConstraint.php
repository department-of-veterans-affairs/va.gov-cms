<?php

namespace Drupal\va_gov_events\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Provides a NonZeroEventDate constraint.
 *
 * @Constraint(
 *   id = "NonZeroEventDate",
 *   label = @Translation("NonZeroEventDate", context = "Validation"),
 * )
 */
class NonZeroEventDateConstraint extends Constraint {

  /**
   * Constraint error message.
   *
   * @var string
   */
  public $errorMessage = 'Please add one recurring event to the event series.';

}
