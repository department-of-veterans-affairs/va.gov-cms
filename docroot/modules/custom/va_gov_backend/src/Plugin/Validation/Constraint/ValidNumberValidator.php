<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ValidNumber constraint.
 */
class ValidNumberValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      if (!preg_match("/^\d{3}-\d{3}-\d{4}$/", $item->value)) {
        // Not a valid phone number.
        $this->context->addViolation($constraint->notValid, ['%value' => $item->value]);
      }

    }
  }

}
