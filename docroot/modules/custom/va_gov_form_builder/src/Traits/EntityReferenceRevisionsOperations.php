<?php

namespace Drupal\va_gov_form_builder\Traits;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityConstraintViolationList;

/**
 * Traits for Entity Reference Revisions (paragraphs) operations.
 */
trait EntityReferenceRevisionsOperations {

  /**
   * Returns violations for single or nested entity reference revision entities.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to validate recursively.
   * @param mixed<EntityConstraintViolationList> $violations
   *   Violations to add to the list.
   *
   * @return \Drupal\Core\Entity\EntityConstraintViolationListInterface
   *   The comprehensive violations list for referenced the referenced entity.
   */
  public function recursiveEntityReferenceRevisionValidator(ContentEntityInterface $entity, mixed $violations = []): EntityConstraintViolationList {
    $violations->addAll($entity->validate());
    foreach ($entity->getFields() as $field) {
      // Skip base fields.
      if ($field->getFieldDefinition()->getFieldStorageDefinition()->isBaseField()) {
        continue;
      }
      // Check if it's an entity reference field.
      if ($field->getFieldDefinition()->getType() === 'entity_reference_revisions') {
        // Now recursively check referenced entities (e.g., Paragraphs).
        foreach ($field->referencedEntities() as $referenced_entity) {
          // Only recurse if it's a content entity.
          if ($referenced_entity instanceof ContentEntityInterface) {
            $this->recursiveEntityReferenceRevisionValidator($referenced_entity, $violations);
          }
        }
      }
    }

    return $violations;
  }

}
