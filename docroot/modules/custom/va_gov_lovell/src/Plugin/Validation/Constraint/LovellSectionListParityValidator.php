<?php

namespace Drupal\va_gov_lovell\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the LovellSectionListParity constraint.
 */
class LovellSectionListParityValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    // Only enforce this validator for Lovell sections.
    $lovellTermIDs = [
      '347',
      '1039',
      '1040',
    ];
    $entity = $items->getEntity();
    $sectionTermID = $entity->field_administration->target_id;
    if (in_array($sectionTermID, $lovellTermIDs)) {
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
      $sectionName = $term_storage->load($sectionTermID)->getName();
      foreach ($items as $item) {
        /** @var \Drupal\va_gov_lovell\Plugin\Validation\Constraint\LovellSectionListParity $constraint */
        $listPage = $node_storage->load($item->target_id);
        if ($listPage->field_administration->target_id !== $sectionTermID) {
          $this->context->addViolation($constraint->notSectionListMatch, ['%value' => $sectionName]);
          return;
        }
      }
    }
  }

}
