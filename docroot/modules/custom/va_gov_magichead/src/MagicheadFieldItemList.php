<?php

namespace Drupal\va_gov_magichead;

use Drupal\entity_reference_hierarchy_revisions\EntityReferenceHierarchyRevisionsFieldItemList;

/**
 * FieldItemList class for magicfield.
 */
class MagicheadFieldItemList extends EntityReferenceHierarchyRevisionsFieldItemList {

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
