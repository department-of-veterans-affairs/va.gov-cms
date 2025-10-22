<?php

namespace Drupal\va_gov_facilities\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Provides a constraint to keep 'closed' from being selected.
 *
 * @Constraint(
 *   id = "ClosedOperatingStatus",
 *   label = @Translation("ClosedOperatingStatus", context = "Validation"),
 * )
 */
class ClosedOperatingStatusConstraint extends Constraint {

  /**
   * The error message to display.
   *
   * @var string
   */
  public $errorMessage = 'The operating status "Closed" is deprecated and can no longer be selected directly. Please use "Temporary facility closure" instead.';

}
