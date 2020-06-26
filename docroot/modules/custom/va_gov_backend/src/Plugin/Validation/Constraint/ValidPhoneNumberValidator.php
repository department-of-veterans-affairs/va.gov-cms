<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ValidPhoneNumber constraint.
 */
class ValidPhoneNumberValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      // Greater than 9 numbers, so we do a phone number check.
      if (!preg_match("/^\d{3}-\d{3}-\d{4}$/", $item->value) && strlen($item->value) > 9) {
        $this->context->addViolation($constraint->notValidTel, ['%value' => $item->value]);
      }
      // Length is 5 or 6, so do a shortcode check.
      elseif ((!preg_match("/^\d{5}$/", $item->value) && strlen($item->value) === 5) || (!preg_match("/^\d{6}$/", $item->value)&&strlen($item->value) === 6)) {
        $this->context->addViolation($constraint->notValidSms, ['%value' => $item->value]);
      }
      // Length is 3, so do a TTY check.
      elseif ((!preg_match("/^\d{3}$/", $item->value)) && (strlen($item->value) === 3)) {
        $this->context->addViolation($constraint->notValidTty, ['%value' => $item->value]);
      }
      // Length doesn't match any phone type.
      elseif (strlen($item->value) !== 3 && strlen($item->value) !== 5 && strlen($item->value) !== 6 && strlen($item->value) !== 12) {
        $this->context->addViolation($constraint->notValidLength, ['%value' => $item->value]);
      }
    }
  }

}
