<?php

namespace Drupal\va_gov_vamc\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\node\NodeInterface;


/**
 * Validates the FacilityParentLinkChecker constraint.
 */
class FacilityParentLinkCheckerValidator extends ConstraintValidator {

  /**
   * The form entry for the current menu parent.
   *
   * @var string
   */
  protected $currentMenuParent = '';


  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $menu = $items->menu;
    if ($menu) {
      $menu_parent = $menu['menu_parent'];
      if (!empty($menu_parent)) {
        // When a menu parent selection is the menu root, it ends with ":".
        $no_menu_parent_pattern = '/:$/';
        if (preg_match($no_menu_parent_pattern,$menu_parent)) {
          // The pattern for a VAMC system menu root (placeholder only).
          $menu_root = '<VA [Place name] health care>';
          $this->context->addViolation($constraint->parentLinkNotCorrect, [
            '@menu_root' => $menu_root,
          ]);
          return;
        }
      }
    }
  }
}
