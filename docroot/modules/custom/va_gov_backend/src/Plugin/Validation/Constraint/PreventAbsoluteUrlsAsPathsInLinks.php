<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the text does not use any absolute URLs as paths.
 *
 * In other words, we want to avoid things like this:
 *
 * `<a href="/https://www.va.gov/">VA.gov</a>`
 *
 * @Constraint(
 *   id = "PreventAbsoluteUrlsAsPathsInLinks",
 *   label = @Translation("Prevent Absolute URLs as Paths in Links", context = "Validation"),
 *   type = { "string_long", "text_long" }
 * )
 */
class PreventAbsoluteUrlsAsPathsInLinks extends Constraint {

  /**
   * The error message for plain text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventProtocolRelativeLinksValidator
   */
  public $plainTextMessage = 'Review the link ":url" and update the URL to remove the leading slash.';

  /**
   * The error message for rich text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventProtocolRelativeLinksValidator
   */
  public $richTextMessage = 'Review the linked text ":link" (:url) and update the URL to remove the leading slash.';

}
