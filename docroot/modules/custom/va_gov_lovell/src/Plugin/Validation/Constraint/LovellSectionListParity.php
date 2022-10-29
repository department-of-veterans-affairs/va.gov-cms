<?php

namespace Drupal\va_gov_lovell\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validates that a Lovell Section choice matches the listing page choice.
 *
 * @Constraint(
 *   id = "LovellSectionListParity",
 *   label = @Translation("Lovell Section List Parity", context = "Validation"),
 *   type = "string"
 * )
 */
class LovellSectionListParity extends Constraint {

  /**
   * The message shown if the listing page value does not match the Lovell section.
   *
   * @var string
   * @see \Drupal\va_gov_lovell\Plugin\Validation\Constraint\LovellSectionListParityValidator
   */
  public $notSectionListMatch = 'Please select "%validSelection" for the current section: "%section".';

}
