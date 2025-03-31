<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for ensuring the submitted values are numeric.
 *
 * @Constraint(
 *   id = "ValidNumeric",
 *   label = @Translation("Valid number", context = "Validation"),
 *   type = "string"
 * )
 */
class ValidNumeric extends Constraint {

  /**
   * Error message shown when an extension contains non-numeric characters.
   *
   * @var string
   */
  public string $notANumber = 'Enter an extension that contains only numeric characters (0-9).';

}
