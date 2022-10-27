<?php

namespace Drupal\va_gov_lovell\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is a valid number.
 *
 * @Constraint(
 *   id = "LovellSectionListParity",
 *   label = @Translation("Lovell Section List Parity", context = "Validation"),
 *   type = "string"
 * )
 */
class LovellSectionListParity extends Constraint {

  /**
   * The message that will be shown if the value does not match the section.
   *
   * @var string
   * @see \Drupal\va_gov_lovell\Plugin\Validation\Constraint\LovellSectionListParityValidator
   */
  public $notSectionListMatch = 'The selected list page is not within the %value section.';

}
