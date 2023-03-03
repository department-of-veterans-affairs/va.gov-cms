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
   * The message that will be shown if the Facility is not in the chosen Section.
   *
   * @see \Drupal\va_gov_vamc\Plugin\Validation\Constraint\SectionMatcherValidator
   */
  public $notSectionMatchFacility = 'The Facility does not match the Section. Please select a matching option.';

  /**
   * The message that will be shown if the VAMC system health service is not in the chosen Section.
   *
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\SectionMatcherValidator
   */
  public $notSectionMatchService = 'The VAMC system health service does not match the Section. Please select a matching option.';

  /**
   * The message that will be shown if the value is not a valid tty.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\SectionMatcherValidator
   */
  public $notSectionMatchEither = 'The Facility and VAMC system health service do not match the section. Please select matching options.';


}
