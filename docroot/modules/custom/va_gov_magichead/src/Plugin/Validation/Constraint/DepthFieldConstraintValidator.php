<?php

namespace Drupal\va_gov_magichead\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the DepthFieldConstraint constraint.
 */
class DepthFieldConstraintValidator extends ConstraintValidator {

  /**
   * Checks that depth field value != >1 than the one before it
   * This one minus previous one is 1 or less, can be negative
   *
   * @param $value
   *   The field values.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint for the validation.
   */
  public function validate($value, Constraint $constraint) {
    // Return early if we don't have more than 1 value.
    if (count($value) < 2) {
      return;
    }
    foreach ($value as $delta => $item) {
      $values = $item->getValue();
      $currentItemDepth = $values['depth'];
      if ($delta > 0) {
        if (($currentItemDepth - $lastItemDepth) > 1) {
          $this->context->buildViolation($constraint->errorMessage)
            ->atPath((string) $delta)
            ->addViolation();
        }
      }
      $lastItemDepth = $currentItemDepth;
    }
  }
}
