<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the QaLimit constraint.
 */
class QaLimitValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    $panel_enabled = $this->context->getRoot()->getEntity()->get('field_clp_faq_panel')->getString();
    $number = $item->count();
    if ($panel_enabled && $number < 3 || $number > 10) {
      $this->context->addViolation($constraint->outOfRange, ['%number' => $number]);
    }
  }

}
