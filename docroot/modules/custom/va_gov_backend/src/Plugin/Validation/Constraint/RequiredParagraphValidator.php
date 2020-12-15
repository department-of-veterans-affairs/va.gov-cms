<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the RequiredParagraph constraint.
 */
class RequiredParagraphValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    $panel_enabled = $this->context->getRoot()->getEntity()->get($constraint->toggle)->getString();
    $number = $item->count();
    if ($panel_enabled && $number < $constraint->min) {
      $this->context->addViolation($constraint->tooFew, [
        '%number' => $number,
        '%min' => $constraint->min,
        '%paragraph' => $constraint->readable,
      ]);
    }
    elseif ($panel_enabled && $number > $constraint->max) {
      $this->context->addViolation($constraint->tooMany, [
        '%number' => $number,
        '%max' => $constraint->max,
        '%paragraph' => $constraint->readable,
      ]);
    }
    elseif ($panel_enabled && empty($item)) {
      $this->context->addViolation($constraint->required, [
        '%paragraph' => $constraint->readable,
      ]);
    }
  }

}
