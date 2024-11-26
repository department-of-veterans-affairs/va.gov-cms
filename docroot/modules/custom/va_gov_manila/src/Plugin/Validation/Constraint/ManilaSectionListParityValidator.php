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
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    // If we don't have a section term id, we can't get the section name.
    if (!isset($sectionTermID)) {
      return;
    }
    $sectionName = $term_storage->load($sectionTermID)->getName();
    // If we don't have any items, don't bother looping.
    if (count($items) === 0) {
      return;
    }
    foreach ($items as $item) {
      if (!isset($item->target_id)) {
        continue;
      }
      $listPage = $node_storage->load($item->target_id);
      if (($sectionTermID === $this->manilaVaSystemId)
        || $listPage->field_administration->target_id === $this->manilaVaSystemId) {
        if ($listPage->field_administration->target_id !== $sectionTermID) {
          /** @var \Drupal\va_gov_manila\Plugin\Validation\Constraint\ManilaSectionListParity $constraint */
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
