<?php

namespace Drupal\va_gov_magichead\Plugin\Field\FieldType;

use Drupal\entity_reference_hierarchy_revisions\Plugin\Field\FieldType\EntityReferenceHierarchyRevisionsItem;

/**
 * Defines the 'magichead' field type.
 *
 * @FieldType(
 *   id = "magichead",
 *   label = @Translation("Magichead"),
 *   category = @Translation("Reference revisions"),
 *   default_widget = "magichead_paragraphs_classic",
 *   default_formatter = "entity_reference_label",
 *   list_class = "\Drupal\entity_reference_hierarchy_revisions\EntityReferenceHierarchyRevisionsFieldItemList",
 * )
 */
class MagicheadItem extends EntityReferenceHierarchyRevisionsItem {

  /**
   * {@inheritDoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();
    $constraint_manager = $this->getTypedDataManager()->getValidationConstraintManager();
    $constraints[] = $constraint_manager->create('MagicheadDepthFieldConstraint', []);
    return $constraints;
  }

}
