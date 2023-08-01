<?php

namespace Drupal\va_gov_profile\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Constrains conditionally required fields if set to be a profile page.
 *
 * @Constraint(
 *   id = "PersonPageRequiredFieldsConstraint",
 *   label = @Translation("Constrain conditionally required fields", context="Validation"),
 *   type = "entity"
 * )
 */
class PersonPageRequiredFieldsConstraint extends Constraint {

  /**
   * Message shown when validation fails.
   *
   * @var string
   */
  public $message = "In order to use '@checkbox' a profile page with biography, the @empty_fields must not be empty.";

}
