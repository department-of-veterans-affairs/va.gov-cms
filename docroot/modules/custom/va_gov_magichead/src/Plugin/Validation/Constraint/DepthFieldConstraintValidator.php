<?php

namespace Drupal\va_gov_magichead\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the DepthFieldConstraint constraint.
 */
class DepthFieldConstraintValidator extends ConstraintValidator {

  /**
   * Validates the depth property is set correctly.
   *
   * @param mixed $value
   *   The field values.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint for the validation.
   */
  public function validate($value, Constraint $constraint) {
    $values = $value->getValue();
    $lastItemDepth = 0;
    foreach ($values as $delta => $value) {
      $currentItemDepth = $value['depth'];
      if (($currentItemDepth - $lastItemDepth) > 1) {
        $this->context->buildViolation($constraint->errorMessage)->atPath((string) $delta . '.depth')->addViolation();
      }
      $lastItemDepth = $currentItemDepth;
    }
  }

}
