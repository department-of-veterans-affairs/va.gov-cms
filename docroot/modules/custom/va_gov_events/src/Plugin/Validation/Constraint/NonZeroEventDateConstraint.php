<?php

namespace Drupal\va_gov_events\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Provides a NonZeroEventDate constraint.
 *
 * @Constraint(
 *   id = "NonZeroEventDate",
 *   label = @Translation("NonZeroEventDate", context = "Validation"),
 * )
 *
 * @DCG
 * To apply this constraint on third party entity types implement either
 * hook_entity_base_field_info_alter() or hook_entity_bundle_field_info_alter().
 */
class NonZeroEventDateConstraint extends Constraint {

  /**
   * Constraint error message.
   *
   * @var string
   */
  public $errorMessage = 'Events cannot be created without a start and end date.';

}
