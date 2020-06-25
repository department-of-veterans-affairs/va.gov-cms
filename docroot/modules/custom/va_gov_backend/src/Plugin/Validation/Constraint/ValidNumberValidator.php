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
      // Check for phone number, then short codes, then tty. No match = proceed.
      if (!preg_match("/^\d{3}-\d{3}-\d{4}$/", $item->value) && !preg_match("/^\d{3}$/", $item->value) && !preg_match("/^\d{5}$/", $item->value) && !preg_match("/^\d{6}$/", $item->value)) {
        $this->context->addViolation($constraint->notValid, ['%value' => $item->value]);
      }
    }
  }

}
