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
   * @param mixed $items
   *   The field values.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint for the validation.
   */
  public function validate($items, Constraint $constraint) {
    if (!$items instanceof MagicheadFieldItemList) {
      return;
    }
    $lastItemDepth = 0;
    $fieldDefinition = $items->getFieldDefinition();
    $max_depth = $fieldDefinition->getSetting('max_depth');
    foreach ($items as $delta => $item) {
      $currentItemDepth = intval($item->get('depth')->getValue());
      // Validate depth is not negative.
      if ($currentItemDepth < 0) {
        $this->context->buildViolation($constraint->negativeDepthErrorMessage)->atPath((string) $delta . '.depth')->addViolation();
      }
      // Validate depth is not skipped.
      if (($currentItemDepth - $lastItemDepth) > 1) {
        $this->context->buildViolation($constraint->skippedDepthErrorMessage)->atPath((string) $delta . '.depth')->addViolation();
      }
      // Validate depth does not exceed max depth.
      if (isset($max_depth) && ($currentItemDepth > $max_depth)) {
        $this->context->buildViolation($constraint->maximumDepthErrorMessage, [':max' => $max_depth])->atPath((string) $delta . '.depth')->addViolation();
      }
      $lastItemDepth = $currentItemDepth;
    }
  }

}
