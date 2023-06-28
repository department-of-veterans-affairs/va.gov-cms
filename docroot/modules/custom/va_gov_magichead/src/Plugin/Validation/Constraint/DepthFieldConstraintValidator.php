<?php

namespace Drupal\va_gov_magichead\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the DepthFieldConstraint constraint.
 */
class DepthFieldConstraintValidator extends ConstraintValidator {

  /**
   * Checks that depth field value != >1 than the one before it
   * This one minus previous one is 1 or less, can be negative
   *
   * @param $value
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint for the validation.
   */
  public function validate($value, Constraint $constraint) {
//    // Get field's parent entity to access other fields on this entity.
//    /** @var \Drupal\entity_reference_hierarchy_revisions\Entity\STUFF $entity */
//    $entity = $field->getEntity();
//
//    if ($entity &&
//      $entity->hasField('field_va_benefit_eligibility[0][depth]') &&
//      $entity->hasField('ALSO THAT but the next one (or previous one)?')
//    ) {
//      // Get the value of the first depth field.
//      /** @var  */
//      $thisDepth = $entity->STUFF;
//      // Get the value of the previous depth field.
//      /** @var  */
//      $thatDepth = $entity->STUFF;
//
//      // Display an error message, if difference is more than 1.
//      if ( $thisDepth - $thatDepth >= '1') {
        $this->context->buildViolation($constraint->errorMessage)
          ->atPath('field_test')
          ->addViolation();
//      }
//    }

  }

}
