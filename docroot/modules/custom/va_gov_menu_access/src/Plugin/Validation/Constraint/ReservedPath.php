<?php

namespace Drupal\va_gov_menu_access\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the node alias isnt't reserved.
 *
 * @Constraint(
 *   id = "ReservedPath",
 *   label = @Translation("Reserved Path", context = "Validation"),
 *   type = "string"
 * )
 */
class ReservedPath extends Constraint {

  /**
   * The message that will be shown if the alias is reserved.
   *
   * @var \Drupal\va_gov_menu_access\Plugin\Validation\Constraint
   */
  public $pathIsReserved = 'The path alias you have created has already been reserved.';

}
