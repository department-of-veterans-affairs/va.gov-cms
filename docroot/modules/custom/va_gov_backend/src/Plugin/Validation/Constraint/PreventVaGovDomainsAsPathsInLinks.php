<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the text does not contain any VA.gov domains as path segments.
 *
 * For instance, we want to avoid links like this:
 *
 * `<a href="/www.va.gov/philadelphia-experiment">Philadelphia Experiment</a>`
 *
 * @Constraint(
 *   id = "PreventVaGovDomainsAsPathsInLinks",
 *   label = @Translation("Prevent VA.gov URLs as Paths in Links", context = "Validation"),
 *   type = { "string_long", "text_long" }
 * )
 */
class PreventVaGovDomainsAsPathsInLinks extends Constraint {

  /**
   * The error message for plain text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventVaGovDomainsAsPathsInLinksValidator
   */
  public $plainTextMessage = 'Review the link ":url" and replace the leading slash with https://.';

  /**
   * The error message for rich text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventVaGovDomainsAsPathsInLinksValidator
   */
  public $richTextMessage = 'Review the linked text ":link" (:url) and replace the leading slash with https://.';

}
