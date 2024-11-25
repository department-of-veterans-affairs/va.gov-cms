<?php

namespace Drupal\va_gov_manila\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ManilaSectionListParity constraint.
 */
class ManilaSectionListParityValidator extends ConstraintValidator {

  /**
   * The Manila VA system Section id.
   *
   * @var int
   */
  protected $manilaVaSystemId = '1187';

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    // This validator only applies to events, stories and news releases.
    // This validator is attached to the field_listing field.
    // Check to see if the section for the current entity is a Manila section.
    // If it is, load the listing page referenced by field_listing.
    // If the section for the listing page doesn't match throw an error.
    $entity = $items->getEntity();
    $sectionTermID = $entity->field_administration->target_id;
    $fieldLabel = $items->getFieldDefinition()->getLabel();
    if ($sectionTermID === $this->manilaVaSystemId) {
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
      $sectionName = $term_storage->load($sectionTermID)->getName();
      foreach ($items as $item) {
        /** @var \Drupal\va_gov_manila\Plugin\Validation\Constraint\ManilaSectionListParity $constraint */
        $listPage = $node_storage->load($item->target_id);
        if ($listPage->field_administration->target_id !== $sectionTermID) {
          $this->context->addViolation($constraint->notSectionListMatch, [
            '%section' => $sectionName,
            '%fieldLabel' => $fieldLabel,
          ]);
          return;
        }
      }
    }
  }

}
