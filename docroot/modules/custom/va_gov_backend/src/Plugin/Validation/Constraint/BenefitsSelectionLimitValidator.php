<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the BenefitsSelectionLimit constraint.
 */
class BenefitsSelectionLimitValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    if ($item->count() > 2) {
      $this->context->addViolation($constraint->moreThanTwo);
    }
  }

}
