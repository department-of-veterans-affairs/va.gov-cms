<?php

namespace Drupal\va_gov_facilities\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that a timezone was not included in office hours comments.
 *
 * @Constraint(
 *   id = "DisallowTimezone",
 *   label = @Translation("Disallow Timezone", context = "Validation"),
 *   type = "string"
 * )
 */
class DisallowTimezone extends Constraint {

  /**
   * The message that will be shown if the comment contains a timezone.
   *
   * @var string
   * @see \Drupal\va_gov_facilities\Plugin\Validation\Constraint\DisallowTimezoneValidator
   */
  public $timezoneFound = 'Please avoid timezones in comments.';

}
