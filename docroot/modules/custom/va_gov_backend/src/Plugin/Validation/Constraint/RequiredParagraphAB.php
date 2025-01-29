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

  /**
   * Displays validation error as Drupal message when no field values exist.
   *
   * @var bool
   */
  public $requiredErrorDisplayAsMessage;

  /**
   * The plural label.
   *
   * @var string
   */
  public $pluralLabel;

  /**
   * The panel label.
   *
   * @var string
   */
  public $panelLabel;

  /**
   * The message that will be shown if the paragraph number is less than min.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\RequiredParagraphABValidator
   */
  public $tooFew = 'Add %plurlLabel. A minimum of %min %readables is required.';

  /**
   * The message that will be shown if the paragraph number is more than max.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\RequiredParagraphABValidator
   */
  public $tooMany = 'Remove %plurlLabel. A maximum of %max %readables is allowed.';

  /**
   * The message that will be shown if the paragraph is empty.
   *
   * @var string
   * @see \Drupal\va_gov_backend\Plugin\Validation\Constraint\RequiredParagraphABValidator
   */
  public $required = 'A minimum of %min %readables is required when the %panelLabel page segment is enabled. Disable the FAQs page segment if there are no %readables to add.';

}
