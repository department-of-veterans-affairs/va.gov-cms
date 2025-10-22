<?php

namespace Drupal\va_gov_facilities\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ClosedOperatingStatus constraint.
 */
class ClosedOperatingStatusConstraintValidator extends ConstraintValidator {

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
        $this->context->addViolation($constraint->errorMessage);
      }
    }
  }

}
