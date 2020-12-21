<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that a pragraph has been created when required.
 *
 * @Constraint(
 *   id = "RequiredParagraph",
 *   label = @Translation("QA limit", context = "Validation"),
 *   type = "string"
 * )
 */
class RequiredParagraph extends Constraint {

  /**
   * The panel enable boolean.
   *
   * @var string
   */
  public $toggle;

  /**
   * The panel enable boolean human readable name.
   *
   * @var string
   */
  public $readable;

  /**
   * The minimum number of paragraphs required.
   *
   * @var int
   */
  public $min;

  /**
   * The maximum number of paragraphs allowed.
   *
   * @var int
   */
  public $max;

  /**
   * The message that will be shown if the paragraph number is less than min.
   *
   * @var \Drupal\va_gov_backend\Plugin\Validation\Constraint
   */
  public $tooFew = '%number %paragraph entries created. Minimum of %min required.';

  /**
   * The message that will be shown if the paragraph number is more than max.
   *
   * @var \Drupal\va_gov_backend\Plugin\Validation\Constraint
   */
  public $tooMany = '%number %paragraph entries created. Maximum of %max allowed.';

  /**
   * The message that will be shown if the paragraph is empty.
   *
   * @var \Drupal\va_gov_backend\Plugin\Validation\Constraint
   */
  public $required = '%paragraph entry is required.';

}
