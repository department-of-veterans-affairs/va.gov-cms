<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that number of qa's is between 3 and 10.
 *
 * @Constraint(
 *   id = "QaLimit",
 *   label = @Translation("QA limit", context = "Validation"),
 *   type = "string"
 * )
 */
class QaLimit extends Constraint {

  /**
   * The message that will be shown if the value is < 3 or > 10.
   *
   * @var \Drupal\va_gov_backend\Plugin\Validation\Constraint
   */
  public $outOfRange = '%number QA\'s entered. Number cannot be less than 3 or more than 10.';

}
