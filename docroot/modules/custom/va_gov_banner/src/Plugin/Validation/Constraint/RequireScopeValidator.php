<?php

namespace Drupal\va_gov_banner\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the RequireScope constraint.
 */
class RequireScopeValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    /** @var \Drupal\va_gov_banner\Plugin\Validation\Constraint\RequireScope $constraint */
    if ($item->getEntity()->get('moderation_state')->getString() !== 'published') {
      return;
    }
    if (empty($item->getEntity()->get('field_target_paths')->getString())) {
      $this->context->addViolation($constraint->noPaths);
    }
  }

}
