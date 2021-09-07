<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the text does not contain any immediately adjacent links.
 *
 * @Constraint(
 *   id = "PreventAdjacentLinks",
 *   label = @Translation("Prevent Adjacent Links", context = "Validation"),
 *   type = { "text_long" }
 * )
 */
class PreventAdjacentLinks extends Constraint {

  /**
   * The error message for rich text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\ConstraintPreventAdjacentLinksValidator
   */
  public $message = 'The link ":link" is too close to the following link, ":link2".  Please ensure that links are separated by whitespace.';

}
