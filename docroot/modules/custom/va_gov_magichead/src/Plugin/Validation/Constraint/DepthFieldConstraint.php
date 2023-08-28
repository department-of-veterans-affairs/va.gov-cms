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
   * Skipped depth error message.
   *
   * @var string
   */
  public $skippedDepthErrorMessage = 'Depth for a Section cannot be more than 1 greater than the Section immediately above it';

  /**
   * Netative depth error message.
   *
   * @var string
   */
  public $negativeDepthErrorMessage = 'Depth must be a positive integer';

  /**
   * Maximum depth exceeded error message.
   *
   * @var string
   */
  public $maximumDepthErrorMessage = 'Maximum depth allowed is :max';

}
