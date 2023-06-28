<?php

namespace Drupal\va_gov_magichead\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Provides a DepthFieldConstraint constraint.
 *
 * @Constraint(
 *   id = "MagicheadDepthFieldConstraint",
 *   label = @Translation("DepthFieldConstraint", context = "Validation"),
 * )
 */
class DepthFieldConstraint extends Constraint {

  /**
   * Constraint error message.
   *
   * @var string
   */
  public $errorMessage = 'Doh! This is an error message';

}
