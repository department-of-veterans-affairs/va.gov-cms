<?php

namespace Drupal\va_gov_magichead\Plugin\Validation\Constraint;

use Drupal\va_gov_magichead\Plugin\Field\FieldType\MagicheadItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the DepthFieldConstraint constraint.
 */
class DepthFieldConstraintValidator extends ConstraintValidator {

  protected static $lastItemDepth;

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
    // Return early if we don't have the right type of field.
    if (!$value instanceof MagicheadItem) {
      return;
    }
    if (isset(self::$lastItemDepth)) {
      $values = $value->getValue();
      $currentItemDepth = $values['depth'];
      if (($currentItemDepth - self::$lastItemDepth) > 1) {
        $this->context->buildViolation($constraint->errorMessage)->addViolation();
      }
      self::$lastItemDepth = $currentItemDepth;
    }
    else {
      self::$lastItemDepth = 0;
    }
  }

}
