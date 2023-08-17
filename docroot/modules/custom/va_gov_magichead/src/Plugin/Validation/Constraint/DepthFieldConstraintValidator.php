<?php

namespace Drupal\va_gov_magichead\Plugin\Validation\Constraint;

use Drupal\va_gov_magichead\MagicheadFieldItemList;
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
    if (!$value instanceof MagicheadFieldItemList) {
      return;
    }
    $values = $value->getValue();
    $lastItemDepth = 0;
    $fieldDefinition = $value->getFieldDefinition();
    $max_depth = $fieldDefinition->getSetting('max_depth');
    foreach ($values as $delta => $value) {
      $currentItemDepth = intval($value['depth']);
      // Validate depth is not negative.
      if ($currentItemDepth < 0) {
        $this->context->buildViolation($constraint->negativeDepthErrorMessage)->atPath((string) $delta . '.depth')->addViolation();
      }
      // Validate depth is not skipped.
      if (($currentItemDepth - $lastItemDepth) > 1) {
        $this->context->buildViolation($constraint->skippedDeptherrorMessage)->atPath((string) $delta . '.depth')->addViolation();
      }
      // Validate depth is not exceeding max depth.
      if ($currentItemDepth > $max_depth) {
        $this->context->buildViolation($constraint->maximumDepthErrorMessage, [':max' => $max_depth])->atPath((string) $delta . '.depth')->addViolation();
      }
      $lastItemDepth = $currentItemDepth;
    }
  }

}
