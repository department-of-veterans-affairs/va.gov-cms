<?php

namespace Drupal\va_gov_vamc\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value matches the Section.
 *
 * @Constraint(
 *   id = "SectionMatcher",
 *   label = @Translation("Matching Section", context = "Validation"),
 *   type = "string"
 * )
 */
class SectionMatcher extends Constraint {

  /**
   * Shown if Facility or VAMC system health service do not match Section.
   *
   * @var string
   * @see \Drupal\va_gov_vamc\Plugin\Validation\Constraint\SectionMatcherValidator
   */
  public $notSectionMatch = 'The selected option for <strong>%fieldLabel</strong> is not part of the <strong>%section</strong> section. Please choose a different option or change the section settings to match.';

}
