<?php

namespace Drupal\va_gov_vamc\Plugin\Validation\Constraint;

use Drupal\node\NodeInterface;
use Drupal\va_gov_backend\Plugin\Validation\Constraint\Traits\ValidatorContextAccessTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the MenuParentLink constraint.
 */
class MenuParentLinkValidator extends ConstraintValidator {

  use ValidatorContextAccessTrait;

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {
    if ($value instanceof NodeInterface && $value->bundle() === 'health_care_local_facility') {
      $moderationState = $value->moderation_state->value;
      $menuParent = $value->menu['menu_parent'];

      if (!empty($menuParent) && !empty($moderationState)) {
        $menuIdArray = explode('menu_link_content:', $menuParent);
        // If no second element, parent link is the root. Don't publish.
        if (empty($menuIdArray[1]) && $moderationState === 'published') {
          /** @var \Drupal\va_gov_vamc\Plugin\Validation\Constraint\MenuParentLink $constraint */
          $this->getContext()
            ->buildViolation($constraint->parentLink, [])
            ->addViolation();
        }
      }
    }

  }

}
