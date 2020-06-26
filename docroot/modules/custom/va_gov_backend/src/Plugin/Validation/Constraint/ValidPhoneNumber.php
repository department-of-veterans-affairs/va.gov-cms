<?php

namespace Drupal\va_gov_backend\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is a valid number.
 *
 * @Constraint(
 *   id = "ValidPhoneNumber",
 *   label = @Translation("Valid Number", context = "Validation"),
 *   type = "string"
 * )
 */
class ValidPhoneNumber extends Constraint {

  /**
   * The message that will be shown if the value is not a valid phone number.
   *
   * @var \Drupal\va_gov_backend\Plugin\Validation\Constraint
   */
  public $notValidTel = '%value is not a valid phone number';

  /**
   * The message that will be shown if the value is not a valid shortcode.
   *
   * @var \Drupal\va_gov_backend\Plugin\Validation\Constraint
   */
  public $notValidSms = '%value is not a valid shortcode';

  /**
   * The message that will be shown if the value is not a valid tty.
   *
   * @var \Drupal\va_gov_backend\Plugin\Validation\Constraint
   */
  public $notValidTty = '%value is not a valid TTY';

  /**
   * The message that will be shown if the value is not a valid number length.
   *
   * @var \Drupal\va_gov_backend\Plugin\Validation\Constraint
   */
  public $notValidLength = '%value is not a valid number length';

}
