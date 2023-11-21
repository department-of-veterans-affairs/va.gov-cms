<?php

namespace Drupal\va_gov_events\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Provides an Email to va.gov constraint.
 *
 * @Constraint(
 *   id = "EmailToVaOnly",
 *   label = @Translation("Email to va.gov", context = "Validation"),
 * )
 */
class EmailToVaOnlyConstraint extends Constraint {

  /**
   * The error message.
   *
   * @var string
   */
  public $errorMessage = 'The email must be a VA.gov email address.';

}
