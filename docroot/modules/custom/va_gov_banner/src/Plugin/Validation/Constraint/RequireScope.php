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
   * The message that will be shown if scope is empty.
   *
   * @var string
   * @see \Drupal\va_gov_banner\Plugin\Validation\Constraint\RequireScopeValidator
   */
  public $noPaths = 'Please add at least one path before you publish this banner.';

}
