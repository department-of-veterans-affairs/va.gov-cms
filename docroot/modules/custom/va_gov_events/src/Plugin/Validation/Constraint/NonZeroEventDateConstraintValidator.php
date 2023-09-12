<?php

namespace Drupal\va_gov_events\Plugin\Validation\Constraint;

use Drupal\smart_date\Plugin\Field\FieldType\SmartDateFieldItemList;
use Drupal\smart_date_recur\Entity\SmartDateRule;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the NonZeroEventDate constraint.
 */
class NonZeroEventDateConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    /** @var \Drupal\va_gov_events\Plugin\Validation\Constraint\NonZeroEventDateConstraint $constraint */
    if (!$items instanceof SmartDateFieldItemList) {
      return;
    }
    if (!$constraint instanceof NonZeroEventDateConstraint) {
      return;
    }
    // @phpstan-ignore-next-line/
    $rruleId = $items->rrule;
    if (isset($rruleId)) {
      $rrule = SmartDateRule::load($rruleId);
      $instances = $rrule->getRuleInstances();
      if (count($instances) < 1) {
        $this->context->buildViolation($constraint->errorMessage)->atPath((string) '0.make_recurring')->addViolation();
      }
    }
  }

}
