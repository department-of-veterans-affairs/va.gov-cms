<?php

namespace Drupal\va_gov_vba_facility\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Constrains conditionally required fields if set to be a VBA facility.
 *
 * @Constraint(
 *   id = "VbaFacilityRequiredFieldsConstraint",
 *   label = @Translation("Constrain conditionally required fields", context="Validation"),
 *   type = "entity"
 * )
 */
class VbaFacilityRequiredFieldsConstraint extends Constraint {

  /**
   * Message shown when validation fails.
   *
   * @var string
   */
  public $message = "@empty_fields must not be empty when '@checkbox' is checked.";

}
