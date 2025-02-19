<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the values given are numeric.
 */
class ValidNumericValidator extends ConstraintValidator {

  /**
   * {@inheritDoc}
   */
  public function validate(mixed $items, Constraint $constraint) {
    /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\ValidNumeric $constraint */
    foreach ($items as $item) {
      if (!preg_match("/^\d+$/", $item->value)) {
        $this->context->addViolation($constraint->notANumber);
      }
    }
  }

}
