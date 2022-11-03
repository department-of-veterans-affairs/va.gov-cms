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
   * The message shown if the listing page does not match the Lovell section.
   *
   * @var string
   * @see \Drupal\va_gov_lovell\Plugin\Validation\Constraint\LovellSectionListParityValidator
   */
  public $notSectionListMatch = 'The selected option for <strong>%fieldLabel</strong> is not part of the <strong>%section</strong> section. Please choose a different option or change the section settings to match.';

}
