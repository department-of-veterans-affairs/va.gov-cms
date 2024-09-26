<?php

namespace Drupal\va_gov_form_builder\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that an entity is unique among its bundle based on a given field.
 *
 * @Constraint(
 *   id = "FormBuilder_UniqueField",
 *   label = @Translation("Unique Field", context = "Validation"),
 *   type = "string"
 * )
 */
class UniqueField extends Constraint {

  /**
   * The message that will be shown if the value is not unique.
   *
   * @var string
   * @see \Drupal\va_gov_form_builder\Plugin\Validation\Constraint\UniqueFieldValidator
   */
  public $message = 'There is already a :bundle_label in the system with :field_label `:field_value`.';

}
