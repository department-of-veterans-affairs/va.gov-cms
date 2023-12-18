<?php

namespace Drupal\va_gov_events\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Provides a Disallow link to email constraint.
 *
 * @Constraint(
 *   id = "LinkNoMailto",
 *   label = @Translation("Disallow link to email", context = "Validation"),
 * )
 */
class LinkNoMailtoConstraint extends Constraint {

  /**
   * The error message.
   *
   * @var string
   */
  public $errorMessage = 'Links to email addresses are not allowed.';

}
