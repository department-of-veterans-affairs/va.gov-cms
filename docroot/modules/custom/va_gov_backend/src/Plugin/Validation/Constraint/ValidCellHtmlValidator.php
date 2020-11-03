<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use DOMDocument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ValidCellHtml constraint.
 */
class ValidCellHtmlValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    // Make sure error reporting is on.
    libxml_use_internal_errors(TRUE);
    $dom = new DOMDocument();
    $tableString = '';
    // Build up our html string from all cells.
    foreach ($item->value as $key => $cells) {
      if ($key !== 'caption') {
        foreach ($cells as $cell) {
          $tableString .= $cell;
        }
      }
    }
    $dom->loadHTML($tableString);
    // If we have errors, create a message string.
    if (!empty(libxml_get_errors())) {
      $errorString = '';
      $i = 1;
      foreach (libxml_get_errors() as $error) {
        $errorString .= ' ----- ' . $i . ') ' . $error->message;
        $i++;
      }
      // Clear out the messages in buffer.
      libxml_clear_errors();
      // Deliver the bad news.
      $this->context->addViolation($constraint->notValidCellHtml, ['%errorMessage' => $errorString]);
    }
  }

}
