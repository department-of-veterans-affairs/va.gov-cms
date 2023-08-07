<?php

namespace Drupal\va_gov_magichead\Plugin\Validation\Constraint;

use Drupal\va_gov_magichead\Plugin\Field\FieldType\MagicheadItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the DepthFieldConstraint constraint.
 */
class DepthFieldConstraintValidator extends ConstraintValidator {

  /**
   * The depth of the previous item.
   *
   * @var int
   */
  protected static int $lastItemDepth;

  /**
   * Validates the depth property is set correctly.
   *
   * @param mixed $value
   *   The field values.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint for the validation.
   */
  public function validate($value, Constraint $constraint) {
    // Return early if we don't have the right type of field.
    if (!$value instanceof MagicheadItem) {
      return;
    }
    // Return early if we are validating the first item, and set the itemDepth
    // cache for future iterations.
    if ($value->getName() === 0) {
      self::$lastItemDepth = 0;
    }
    elseif (isset(self::$lastItemDepth)) {
      $values = $value->getValue();
      $currentItemDepth = $values['depth'];
      if (($currentItemDepth - self::$lastItemDepth) > 1) {
        $this->context->buildViolation($constraint->errorMessage)->addViolation();
      }
      self::$lastItemDepth = $currentItemDepth;
    }
  }

}
