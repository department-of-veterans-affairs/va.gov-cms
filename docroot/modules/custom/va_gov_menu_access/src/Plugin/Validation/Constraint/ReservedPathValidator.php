<?php

namespace Drupal\va_gov_menu_access\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ReservedPath constraint.
 */
class ReservedPathValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    $current_user_roles = \Drupal::currentUser()->getRoles();
    $admin_roles = [
      'administrator',
      'content_admin',
    ];
    $admin_role_count = count(array_intersect($admin_roles, $current_user_roles));
    // User not admin, so more checking.
    if ($admin_role_count < 1) {
      $node_alias = $item->toUrl()->toString();
      $disallowed_paths_string = \Drupal::config('va_gov_menu_access.settings')->get('va_gov_menu_access.paths');
      $disallowed_paths = explode("\n", $disallowed_paths_string);
      foreach ($disallowed_paths as $path) {
        // Forbidden singular path detector.
        if (trim($path) === trim($node_alias)) {
          $this->context->addViolation($constraint->pathIsReserved, []);
        }
        // Forbidden path pattern detector.
        if (strpos($path, '*') !== FALSE) {
          $path_array = explode('/', $path);
          $last_path_arg = trim(end($path_array));
          $node_alias_array = explode('/', $node_alias);
          $node_alias_path_arg = trim(end($node_alias_array));
          if ($node_alias_path_arg === $last_path_arg) {
            $this->context->addViolation($constraint->pathIsReserved, []);
          }
        }
      }
    }
  }

}
