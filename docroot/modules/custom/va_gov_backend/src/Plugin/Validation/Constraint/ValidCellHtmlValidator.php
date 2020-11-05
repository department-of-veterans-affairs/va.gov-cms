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
    $errors = [];
    // Build up our html string from all cells.
    foreach ($item->value as $row_num => $row_cells) {
      if ($row_num === 'caption') {
        // Caption can't contain any html.
        $caption = $row_cells;
        $stripped_caption = strip_tags($caption);
        if (strcmp($caption, $stripped_caption) !== 0) {
          // They differ so there must have been tags.
          $errors["caption"] = "Caption can not contain html.";
        }
      }
      else {
        foreach ($row_cells as $col_num => $cell) {
          if (!empty(trim($cell))) {
            $dom = new DOMDocument();
            $dom->loadHTML($cell);
            // There may be multiple errors so process all of them.
            $cell_errors = '';
            foreach (libxml_get_errors() as $error) {
              if (!empty($error->message)) {
                $cell_errors .= "- {$error->message}\n";
              }
            }

            if (!empty($cell_errors)) {
              // Adjust the column and row numbers for humans who don't start
              // counting at 0.
              $human_row = $row_num + 1;
              $human_col = $col_num + 1;
              $errors["{$human_row}x{$human_col}"] = $cell_errors;
            }

            // Clear out the messages in buffer.
            libxml_clear_errors();
          }
        }
      }
    }

    // If we have errors, create a message string from the errors.
    $error_string = '';
    if (!empty($errors)) {
      foreach ($errors as $cell_id => $error) {
        $error_string .= "$cell_id - $error\n";
      }

      // Deliver the bad news.
      $this->context->addViolation($constraint->notValidCellHtml, ['%errorMessage' => $error_string]);
    }
  }

}
