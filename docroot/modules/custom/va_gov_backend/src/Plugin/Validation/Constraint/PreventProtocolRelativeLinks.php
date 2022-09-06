<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the text does not contain any Protocol-Relative links.
 *
 * @Constraint(
 *   id = "PreventProtocolRelativeLinks",
 *   label = @Translation("Prevent Protocol-Relative Links", context = "Validation"),
 *   type = { "string_long", "text_long" }
 * )
 */
class PreventProtocolRelativeLinks extends Constraint {

  /**
   * The error message for plain text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventProtocolRelativeLinksValidator
   */
  public $plainTextMessage = 'Review the link ":url" and update to include either http: or https: at the beginning of the URL.';

  /**
   * The error message for rich text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventProtocolRelativeLinksValidator
   */
  public $richTextMessage = 'Review the linked text ":link" (:url) and update to include either http: or https: at the beginning of the URL.';

}
