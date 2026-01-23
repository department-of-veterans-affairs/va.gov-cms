<?php

namespace Drupal\va_gov_vamc\Plugin\Validation\Constraint;

use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ReferralRequired constraint.
 */
class ReferralRequiredValidator extends ConstraintValidator {

  use ValidatorContextAccessTrait;

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    $entity = $value->getEntity();

    $referralRequired = $entity->field_referral_required->value;
    if ($referralRequired === 'not_applicable') {
      /** @var \Drupal\va_gov_vamc\Plugin\Validation\Constraint\ReferralRequired $constraint */
      $this->getContext()
        ->buildViolation($constraint->referralRequiredMsg, [])
        ->addViolation();
    }
  }

}
