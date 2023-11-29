<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PdfCheck constraint.
 */
class PdfCheckValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($item, Constraint $constraint) {
    /** @var \Drupal\va_gov_backend\Plugin\Validation\Constraint\PdfCheck $constraint */
    // Per internal dev meeting, dependency injection doesn't
    // work well with constraints.
    // Drupal contrib constraint modules call services directly.
    // Okay to follow this pattern.
    /** @var \Drupal\Core\File\MimeType\MimeTypeGuesser $mimeTypeGuesserService */
    $mimeTypeGuesserService = \Drupal::service('file.mime_type.guesser');
    if ($mimeTypeGuesserService->guessMimeType($item->uri) !== 'application/pdf') {
      $this->context->addViolation($constraint->notPdfFile, [':file' => $item->uri]);
    }

  }

}
