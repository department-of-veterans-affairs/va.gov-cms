<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the number of VA Benefits selected on CLP is less than two.
 *
 * @Constraint(
 *   id = "BenefitsSelectionLimit",
 *   label = @Translation("Benefits Selection Limit", context = "Validation"),
 *   type = "string"
 * )
 */
class BenefitsSelectionLimit extends Constraint {

  /**
   * The message that will be shown if the value is greater than two.
   *
   * @var \Drupal\va_gov_backend\Plugin\Validation\Constraint
   */
  public $moreThanTwo = 'Please select no more than two VA Benefits';

}
