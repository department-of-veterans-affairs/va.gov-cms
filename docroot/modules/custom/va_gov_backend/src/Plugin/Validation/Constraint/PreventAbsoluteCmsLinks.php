<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the text does not contain any absolute links to the CMS.
 *
 * @Constraint(
 *   id = "PreventAbsoluteCmsLinks",
 *   label = @Translation("Prevent Absolute CMS Links", context = "Validation"),
 *   type = { "string_long", "text_long" }
 * )
 */
class PreventAbsoluteCmsLinks extends Constraint {

  /**
   * The error message for plain text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\ConstraintPreventAbsoluteCmsLinksValidator
   */
  public $plainTextMessage = 'The text contains a URL ( :url ) that\'s only available on the VA network. Update the link to a valid public-facing page.';

  /**
   * The error message for rich text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\ConstraintPreventAbsoluteCmsLinksValidator
   */
  public $richTextMessage = '":link" uses a URL ( :url ) that\'s only available on the VA network. Update the link to a valid public-facing page.';

}
