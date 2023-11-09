<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the text does not use any preview URL links.
 *
 * In other words, we want to avoid things like this:
 *
 * `<a href="https://preview-staging.vfs.va.gov/path/to/content/">VA.gov</a>`
 *
 * @Constraint(
 *   id = "PreventPreviewUrlLinks",
 *   label = @Translation("Prevent Preview URL Links", context = "Validation"),
 *   type = { "string_long", "text_long" }
 * )
 */
class PreventPreviewUrlLinks extends Constraint {

  /**
   * The error message for plain text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventPreviewUrlLinksValidator
   */
  public $plainTextMessage = 'Replace the preview URL ":url" with a public URL.';

  /**
   * The error message for rich text fields.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\PreventPreviewUrlLinksValidator
   */
  public $richTextMessage = 'Review the linked text ":link" (:url) and replace the preview URL with a public URL.';

}
