<?php

namespace Drupal\va_gov_events\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the Disallow link to email constraint.
 */
class LinkNoMailtoConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if (!$constraint instanceof LinkNoMailtoConstraint) {
      return;
    }
    foreach ($items->getValue() as $delta => $item) {
      if (preg_match('/^mailto:/i', $item['uri']) === 1) {
        $this->context
          ->buildViolation($constraint->errorMessage)
          ->atPath((string) $delta . '.uri')
          ->addViolation();
      }
    }
  }

}
