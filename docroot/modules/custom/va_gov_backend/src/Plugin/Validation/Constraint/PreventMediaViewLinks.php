<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the text does not contain any relative links to media pages.
 *
 * @Constraint(
 *   id = "PreventMediaViewLinks",
 *   label = @Translation("Prevent Media View Links", context = "Validation"),
 *   type = { "text_long" }
 * )
 */
class PreventMediaViewLinks extends Constraint {

  /**
   * The error message for rich text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\ConstraintPreventMediaViewLinksValidator
   */
  public $richTextMessage = '":link" uses a URL ( :url ) that\'s only available on the VA network. Update the link to a valid public-facing page.';

}
