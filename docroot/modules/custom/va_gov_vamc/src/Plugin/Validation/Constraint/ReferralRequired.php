<?php

namespace Drupal\va_gov_vamc\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Make sure VAMCs cannot be saved with an 'Unspecified' referral required.
 *
 * @Constraint(
 *   id = "ReferralRequired",
 *   label = @Translation("Referral Required Validation", context = "Validation"),
 * )
 */
class ReferralRequired extends Constraint {

  /**
   * Message shown when 'Is a referral required?' is set to Unspecified.
   *
   * @var string
   * @see \Drupal\va_gov_vamc\Plugin\Validation\Constraint\ReferralRequiredValidator
   */
  public $referralRequiredMsg = "'Is a referral required?' is a required field. Please choose either 'Yes' or 'No'.";

}
