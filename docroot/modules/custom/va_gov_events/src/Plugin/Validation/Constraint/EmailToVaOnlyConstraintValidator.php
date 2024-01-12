<?php

namespace Drupal\va_gov_events\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the Email to va.gov constraint.
 */
class EmailToVaOnlyConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if (!$constraint instanceof EmailToVaOnlyConstraint) {
      return;
    }
    foreach ($items->getValue() as $item) {
      $value = $item['value'];
      $domain = substr($value, strpos($value, '@') + 1);
      if ($domain !== 'va.gov') {
        $this->context->addViolation($constraint->errorMessage);
      }
    }
  }

}
