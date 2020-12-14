<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted file link is a pdf.
 *
 * @Constraint(
 *   id = "PdfCheck",
 *   label = @Translation("PDF Check", context = "Validation"),
 *   type = "string"
 * )
 */
class PdfCheck extends Constraint {

  /**
   * The message that will be shown if the value is not a unique title.
   *
   * @var \Drupal\va_gov_backend\Plugin\Validation\Constraint
   */
  public $notPdfFile = '<a target="_blank" href=":file">:file is not a pdf</a>';

}
