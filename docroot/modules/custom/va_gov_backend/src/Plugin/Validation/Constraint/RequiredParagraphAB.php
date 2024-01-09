<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

/**
 * Checks that paragraphs a and b have been created as required.
 *
 * @Constraint(
 *   id = "RequiredParagraphAB",
 *   label = @Translation("Limit Paragraph A and B", context = "Validation"),
 *   type = "string"
 * )
 */
class RequiredParagraphAB extends RequiredParagraph {

  /**
   * The field name of paragraph A.
   *
   * @var string
   */
  public $fieldParagraphA;

  /**
   * The field name of paragraph B.
   *
   * @var string
   */
  public $fieldParagraphB;

}
