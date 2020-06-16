<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is a valid number.
 *
 * @Constraint(
 *   id = "ValidNumber",
 *   label = @Translation("Valid Number", context = "Validation"),
 *   type = "string"
 * )
 */
class ValidNumber extends Constraint {

  /**
   * The message that will be shown if the value is not an integer.
   *
   * @var \Drupal\va_gov_backend\Plugin\Validation\Constraint
   */
  public $notValid = '%value is not a valid phone number';

}
