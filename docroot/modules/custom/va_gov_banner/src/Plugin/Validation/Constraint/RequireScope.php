<?php

namespace Drupal\va_gov_banner\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the scope field is not empty prior to publish.
 *
 * @Constraint(
 *   id = "RequireScope",
 *   label = @Translation("Require Scope", context = "Validation"),
 *   type = "string"
 * )
 */
class RequireScope extends Constraint {

  /**
   * The message that will be shown to admin if scope is empty.
   *
   * @var string
   * @see \Drupal\va_gov_banner\Plugin\Validation\Constraint\RequireScopeValidator
   */
  public $noPathsAdmin = 'Please add at least one path before you publish this banner.';

  /**
   * The message that will be shown to non-admin if scope is empty.
   *
   * @var string
   * @see \Drupal\va_gov_banner\Plugin\Validation\Constraint\RequireScopeValidator
   */
  public $noPathsNonAdmin = 'No paths have been set for this banner, please contact an administrator to define where this banner should appear.';

}
