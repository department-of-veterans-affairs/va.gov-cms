<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the text does not contain any probable domains as path segments.
 *
 * For instance, we want to avoid links like this:
 *
 * `<a href="/www.navy.mil/philadelphia-experiment">Philadelphia Experiment</a>`
 *
 * @Constraint(
 *   id = "PreventDomainsAsPathsInLinks",
 *   label = @Translation("Prevent Domains as Paths in Links", context = "Validation"),
 *   type = { "string_long", "text_long" }
 * )
 */
class PreventDomainsAsPathsInLinks extends Constraint {

  /**
   * The error message for plain text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventDomainsAsPathsInLinksValidator
   */
  public $plainTextMessage = 'Review the link ":url" and replace the leading slash with https://.';

  /**
   * The error message for rich text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventDomainsAsPathsInLinksValidator
   */
  public $richTextMessage = 'Review the linked text ":link" (:url) and replace the leading slash with https://.';

}
