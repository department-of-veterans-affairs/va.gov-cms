<?php

namespace Drupal\va_gov_facilities\Plugin\Validation\Constraint;

use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ClosedOperatingStatus constraint.
 */
class ClosedOperatingStatusConstraintValidator extends ConstraintValidator {

  use ValidatorContextAccessTrait;

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    /** @var ClosedOperatingStatusConstraint $constraint */
    if (!$constraint instanceof ClosedOperatingStatusConstraint) {
      return;
    }

    foreach ($value->getValue() as $item) {
      if (isset($item['value']) && $item['value'] === 'closed') {
        $this->getContext()
          ->buildViolation($constraint->errorMessage, [])
          ->addViolation();
      }
    }
  }

}
