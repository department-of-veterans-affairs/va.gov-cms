<?php

namespace Drupal\va_gov_facilities\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the DisallowTimezone constraint.
 */
class DisallowTimezoneValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      $day = $item->getValue();
      if ($this->commentHasTimezone($day['comment'])) {
        /** @var \Drupal\va_gov_facilities\Plugin\Validation\Constraint\DisallowTimezone $constraint */
        $this->context->addViolation($constraint->timezoneFound);
      }
    }
  }

  /**
   * Check if supplied comment contains a timezone.
   *
   * @param string $comment
   *   The input.
   *
   * @return bool
   *   Returns TRUE if the comment contains a timezone.
   */
  public function commentHasTimezone($comment) {
    if (preg_match("/^[ecmp]s?t$|[^a-z0-9][ecmp]s?t[^a-z0-9]/i", $comment)) {
      return TRUE;
    }
    return FALSE;
  }

}
