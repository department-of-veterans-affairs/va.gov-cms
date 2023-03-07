<?php

namespace Drupal\va_gov_vamc\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the SectionMatcher constraint.
 */
class SectionMatcherValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    $entity = $items->getEntity();
    $sectionTermID = $entity->field_administration->target_id;
    $fieldLabel = $items->getFieldDefinition()->getLabel();
    $nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
    $termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $sectionName = $termStorage->load($sectionTermID)->getName();
    foreach ($items as $item) {
      /** @var \Drupal\va_gov_vamc\Plugin\Validation\Constraint\SectionMatcher $constraint */
      $refEnt = $nodeStorage->load($item->target_id);
      if ($refEnt->field_administration->target_id !== $sectionTermID) {
        $this->context->addViolation($constraint->notSectionMatch, [
          '%section' => $sectionName,
          '%fieldLabel' => $fieldLabel,
        ]);
        return;
      }
    }
  }

}
