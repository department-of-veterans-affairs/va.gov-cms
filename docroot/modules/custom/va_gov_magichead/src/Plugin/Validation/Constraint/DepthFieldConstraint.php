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
  public $skippedDeptherrorMessage = 'Depth for a Section must be within 1 of highest depth above this Section';

  public $negativeDepthErrorMessage = 'Depth must be a positive integer';

  public $maximumDepthErrorMessage = 'Maximum depth allowed is :max';

}
