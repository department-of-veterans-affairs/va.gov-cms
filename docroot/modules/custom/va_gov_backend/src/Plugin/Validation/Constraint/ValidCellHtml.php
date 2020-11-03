<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the table cell has valid html.
 *
 * @Constraint(
 *   id = "ValidCellHtml",
 *   label = @Translation("Valid Cell Html", context = "Validation"),
 *   type = "string"
 * )
 */
class ValidCellHtml extends Constraint {

  /**
   * The message that will be shown if the value is not valid html.
   *
   * @var \Drupal\va_gov_backend\Plugin\Validation\Constraint
   */
  public $notValidCellHtml = 'Table field contains html errors: %errorMessage';

}
