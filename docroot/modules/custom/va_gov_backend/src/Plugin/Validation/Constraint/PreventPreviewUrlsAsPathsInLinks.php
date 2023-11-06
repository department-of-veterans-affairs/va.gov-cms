<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the text does not use any preview URLs as paths.
 *
 * In other words, we want to avoid things like this:
 *
 * `<a href="/https://www.va.gov/">VA.gov</a>`
 *
 * @Constraint(
 *   id = "PreventPreviewUrlsAsPathsInLinks",
 *   label = @Translation("Prevent Preview URLs as Paths in Links", context = "Validation"),
 *   type = { "string_long", "text_long" }
 * )
 */
class PreventPreviewUrlsAsPathsInLinks extends Constraint {

  /**
   * The error message for plain text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventPreviewUrlsAsPathsInLinksValidator
   */
  public $plainTextMessage = 'Remove the preview link ":url" and replace it with a public production URL.';

  /**
   * The error message for rich text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventPreviewUrlsAsPathsInLinksValidator
   */
  public $richTextMessage = 'Review the linked text ":link" (:url) and update the preview URL with a public production URL.';

}
