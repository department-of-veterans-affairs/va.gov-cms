<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the text does not contain any local file links.
 *
 * @Constraint(
 *   id = "PreventLocalFileLinks",
 *   label = @Translation("Prevent Local File Links", context = "Validation"),
 *   type = { "text_long" }
 * )
 */
class PreventLocalFileLinks extends Constraint {

  /**
   * The error message for rich text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\ConstraintPreventLocalFileLinksValidator
   */
  public $message = 'The link ":link" appears to contain a local file URL ( :url ). Please ensure that you are linking to a publicly accessible URL.';

}
