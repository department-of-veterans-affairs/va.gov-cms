<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the values given are numeric.
 */
class ValidNumericValidator extends ConstraintValidator {

  use ValidatorContextAccessTrait;

  /**
   * {@inheritDoc}
   */
  public function validate(mixed $items, Constraint $constraint) {
    assert(is_a($constraint, ValidNumeric::class));
    foreach ($items as $delta => $item) {
      if (!preg_match("/^\d+$/", $item->value)) {
        $this->getContext()
          ->buildViolation($constraint->notANumber, [])
          ->atPath($delta . 'value')
          ->addViolation();
      }
    }
  }

}
