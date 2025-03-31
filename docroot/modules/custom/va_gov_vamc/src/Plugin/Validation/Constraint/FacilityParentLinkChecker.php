<?php

namespace Drupal\va_gov_vamc\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value matches the Section.
 *
 * @Constraint(
 *   id = "FacilityParentLinkChecker",
 *   label = @Translation("Empty parent link", context = "Validation"),
 *   type = "entity"
 * )
 */
class FacilityParentLinkChecker extends Constraint {

  /**
   * Shown if VAMC Facility Parent Link is incorrect.
   *
   * @var string
   * @see \Drupal\va_gov_vamc\Plugin\Validation\Constraint\FacilityParentLinkCheckerValidator
   */
  public $parentLinkNotCorrect = 'The Parent link for the facility cannot be @menu_root. It needs to be "------ Locations".';

}
