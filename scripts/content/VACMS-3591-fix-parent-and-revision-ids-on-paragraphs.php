<?php

/**
 * @file
 * One-time migration for repairing rows of improperly cloned paragraphs.
 *
 * After the fix for #3450, we still have some inconsistencies in the database
 * causing issues:
 * - `revision_id` wrong on `paragraphs_item` and `paragraphs_item_field_data`
 * - `parent_id` wrong on `paragraphs_item_data` and
 *     `paragraphs_item_revision_field_data`
 *
 * Our approach here will be:
 *
 * 1. Retrieve a list of all paragraphs with multiple parents.
 * 2. Repair the nodes owning those paragraphs:
 *   A. Determine whether it is a cloned node; if not, no action is necessary.
 *   B. Load the node and its paragraphs, and for each paragraph:
 *     i. Point paragraphs_item.revision_id to the appropriate paragraph
 *        revision ID.
 *     ii. Point paragraphs_item_field_data.revision_id
 * 3. Confirm that no paragraphs exist in the original condition.
 *
 */

use Drupal\entity_clone\EntityClone\EntityCloneInterface;
use Drupal\entity_clone\EntityClone\Content\ContentEntityCloneBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Log a message both to stdout and the Drupal logger.  NOT.
 *
 * @param string $message
 *   The message to log.
 */
function logMessage(string $message): void {
  // \Drupal::logger(__FILE__)->notice($message);
  echo $message . PHP_EOL;
}

/**
 * Retrieve a list of all paragraph item fields on all entities.
 *
 * @return object[]
 *  A list of stdClass objects:
 *    - parent_type (string): an entity type machine name.
 *    - parent_field_name (string): an field machine name.
 */
function getParagraphsFields(): array {
  $database = \Drupal::database();

  $query = $database->select('paragraphs_item_field_data', 'p');
  $query->addField('p', 'parent_type', 'parent_type');
  $query->addField('p', 'parent_field_name', 'parent_field_name');
  $result = $query->distinct()->execute()->fetchAll();

  return $result;
}

/**
 * Retrieve a list of multiply parented paragraph fields with a given parent type and field.
 *
 * Given a parent type and parent field name, we should fetch all of the paragraphs entities
 *  whose revisions do not share a common parent_id.
 *
 * If a paragraph's parent_id changes from revision to revision, it may be the result of an
 *  improper clone.
 *
 * @param string $parent_type
 *   The machine name of the parent entity type.
 * @param string $parent_field_name
 *   The machine name of the parent entity field.
 *
 * @return object[]
 *   A list of stdClass objects, keyed by the paragraph ID:
 *     - target_id (integer): an entity ID.
 *     - parent_ids (array): a list of entity IDs.
 *     - parent_type (string): an entity type machine name.
 *     - parent_field_name (string): a field machine name.
 */
function getMultiplyParentedParagraphFieldRows(string $parent_type, string $parent_field_name): array {
  $database = \Drupal::database();
  $query = $database->select('paragraphs_item_revision_field_data', 'revision');
  $query->condition('parent_type', $parent_type);
  $query->condition('parent_field_name', $parent_field_name);
  $query->addField('revision', 'id', 'target_id');
  $query->addField('revision', 'parent_id', 'parent_id');
  $statement = $query->distinct()->execute();

  $result = [];
  foreach ($statement as $item) {
    $target_id = $item->target_id;
    if (empty($result[$target_id])) {
      $result[$target_id] = (object)[
        'target_id' => $target_id,
        'parent_ids' => [
          $item->parent_id,
        ],
        'parent_type' => $parent_type,
        'parent_field_name' => $parent_field_name,
      ];
    }
    else {
      $result[$target_id]->parent_ids[] = $item->parent_id;
      sort($result[$target_id]->parent_ids);
    }
  }

  $result = array_filter($result, function ($row) {
    return count($row->parent_ids) > 1;
  });

  $count = count($result);
  logMessage("{$parent_type}: {$parent_field_name}... {$count} multiply-parented field rows found.");

  return $result;
}

/**
 * Replace a paragraph with its clone.
 *
 * @param \Drupal\entity_clone\EntityClone\EntityCloneInterface $cloner
 *  A cloner object.
 * @param \Drupal\Core\Entity\ContentEntityInterface $entity
 *  The host entity.
 * @param string $field_name
 *  The name of the paragraph field.
 * @param int $pid
 *  The paragraph entity ID to clone and replace.
 * @param array $properties
 *  Properties used to create the clone paragraph.
 * @param array& $already_cloned
 *  A list of entities already cloned, for use by entity_clone.
 */
function replaceParagraph(EntityCloneInterface $cloner, ContentEntityInterface $entity, string $field_name, int $pid, array $properties, array &$already_cloned): void {
  $field_value = $entity->get($field_name);
  $field_items = $field_value->getValue();
  $referenced_entities = $field_value->referencedEntities();
  $database = \Drupal::database();

  foreach ($field_items as $key => $field_item) {
    if (intval($field_item['target_id']) === $pid) {
      $referenced_entity = $referenced_entities[$key];
      $clone = cloneParagraph($cloner, $referenced_entity, $entity->id(), $properties, $already_cloned);

      $query = $database->update("{$entity->getEntityTypeId()}__{$field_name}");
      $query->fields([
        "{$field_name}_target_id" => $clone->id(),
        "{$field_name}_target_revision_id" => $clone->getRevisionId(),
      ]);
      $query->condition('entity_id', $entity->id());
      $query->condition('revision_id', $entity->getRevisionId());
      $query->condition('delta', $key);
      $query->execute();

      $query = $database->update("{$entity->getEntityTypeId()}_revision__{$field_name}");
      $query->fields([
        "{$field_name}_target_id" => $clone->id(),
        "{$field_name}_target_revision_id" => $clone->getRevisionId(),
      ]);
      $query->condition('entity_id', $entity->id());
      $query->condition('revision_id', $entity->getRevisionId());
      $query->condition('delta', $key);
      $query->execute();

      logMessage("Updated node #{$entity->id()} \"{$entity->getTitle()}\" value #{$key} with new paragraph (was {$pid}, now {$clone->id()}).");
      break;
    }
  }

}

/**
 * Repair a specific paragraph whose parent references are messed up.
 *
 * @param string $parent_type
 *   The machine name of the parent entity type.
 * @param string $parent_field_name
 *   The machine name of the parent entity paragraph field.
 * @param int $parent_id
 *   The unique identifier of the parent entity.
 * @param int $paragraph_id
 *   The unique identifier of the paragraph to repair.
 */
function repairParagraph(string $parent_type, string $parent_field_name, int $parent_id, int $paragraph_id) {
  $database = \Drupal::database();
  $entity_type_manager = \Drupal::entityTypeManager();
  $parent_storage = $entity_type_manager->getStorage($parent_type);
  $parent = $parent_storage->load($parent_id);
  $parent_field = $parent->get($parent_field_name);
  $parent_field_value = $parent_field->getValue();
  foreach ($parent_field_value as $delta => $value) {
    if ((int) $value['target_id'] === $paragraph_id) {
      $revision_id = (int) $value['target_revision_id'];
      logMessage("$parent_type #{$parent_id}->$parent_field_name [$delta] points to revision #$revision_id of paragraph #$paragraph_id.");

      // Check the corresponding row in the paragraphs_item table.
      $query = $database->select('paragraphs_item', 'p');
      $query->addField('p', 'id', 'id');
      $query->addField('p', 'revision_id', 'revision_id');
      $query->condition('id', $paragraph_id);
      $row = $query->execute()->fetchObject();
      $row_revision_id = (int) $row->revision_id;
      if ($revision_id === $row_revision_id) {
        logMessage("paragraphs_item row for $paragraph #$paragraph_id also points to revision #$revision_id... no action needed.");
      }
      else {
        logMessage("paragraphs_item row for $paragraph #$paragraph_id points to revision #$row_revision_id... updating.");
        $query = $database->update("paragraphs_item");
        $query->fields([
          "revision_id" => $revision_id,
        ]);
        $query->condition('id', $paragraph_id);
        $query->condition('revision_id', $row_revision_id);
        $query->execute();
      }

      // Check the corresponding row in the paragraphs_item_field_data table.
      $query = $database->select('paragraphs_item_field_data', 'p');
      $query->addField('p', 'id', 'id');
      $query->addField('p', 'revision_id', 'revision_id');
      $query->addField('p', 'parent_id', 'parent_id');
      $query->addField('p', 'parent_type', 'parent_type');
      $query->condition('id', $paragraph_id);
      $row = $query->execute()->fetchObject();
      $row_revision_id = (int) $row->revision_id;
      $row_parent_id = (int) $row->parent_id;
      $row_parent_type = $row->parent_type;
      if ($parent_type !== $row_parent_type) {
        throw new \Exception("paragraphs_item_field_data #$paragraph_id is pointing to $row_parent_type (expected: $parent_type).  Something is terribly wrong!");
      }
      else if ($revision_id === $row_revision_id && $parent_id === $row_parent_id) {
        logMessage("paragraphs_item_field_data row for $paragraph #$paragraph_id also points to revision #$revision_id and is parented by $parent_type #$parent_id... no action needed.");
      }
      else {
        logMessage("paragraphs_item_field_data row for $paragraph #$paragraph_id points to revision #$row_revision_id (should be $revision_id) and parent $parent_type #$row_parent_id (should be #$parent_id)... updating.");
        $query = $database->update("paragraphs_item_field_data");
        $query->fields([
          "revision_id" => $revision_id,
          "parent_id" => $parent_id,
        ]);
        $query->condition('id', $paragraph_id);
        $query->condition('revision_id', $row_revision_id);
        $query->condition('parent_id', $row_parent_id);
        $query->execute();
      }

      // Check the corresponding row in the paragraphs_item_revision_field_data table.
      $query = $database->select('paragraphs_item_revision_field_data', 'p');
      $query->addField('p', 'id', 'id');
      $query->addField('p', 'revision_id', 'revision_id');
      $query->addField('p', 'parent_id', 'parent_id');
      $query->addField('p', 'parent_type', 'parent_type');
      $query->condition('id', $paragraph_id);
      $query->condition('revision_id', $revision_id);
      $row = $query->execute()->fetchObject();
      $row_revision_id = (int) $row->revision_id;
      $row_parent_id = (int) $row->parent_id;
      $row_parent_type = $row->parent_type;
      if ($parent_type !== $row_parent_type) {
        throw new \Exception("paragraphs_item_revision_field_data #$paragraph_id is pointing to $row_parent_type (expected: $parent_type).  Something is terribly wrong!");
      }
      else if ($parent_id === $row_parent_id) {
        logMessage("paragraphs_item_revision_field_data row for $paragraph #$paragraph_id @ revision #$revision_id is parented by $parent_type #$parent_id... no action needed.");
      }
      else {
        logMessage("paragraphs_item_revision_field_data row for $paragraph #$paragraph_id @ revision #$revision_id is parented by $parent_type #$row_parent_id (should be #$parent_id)... updating.");
        $query = $database->update("paragraphs_item_revision_field_data");
        $query->fields([
          "parent_id" => $parent_id,
        ]);
        $query->condition('id', $paragraph_id);
        $query->condition('revision_id', $row_revision_id);
        $query->condition('parent_id', $row_parent_id);
        $query->execute();
      }

    }
  }
}


/**
 * Main entry point for our script.
 *
 * The affected paragraphs are paragraphs that have revisions whose parent ids are different.
 *
 * After retrieving a list of these paragraphs, we iterate through them, and:
 *   - Skip the first parent; it will keep its paragraph.
 *   - For each remaining parent:
 *     - Clone the paragraph, and
 *     - Update the parent accordingly.
 */
function run() {
  $paragraph_fields = getParagraphsFields();

  foreach ($paragraph_fields as $paragraph_field) {
    $parent_type = $paragraph_field->parent_type;
    $parent_field_name = $paragraph_field->parent_field_name;

    // We only care about cloned nodes right now, because nested paragraphs
    // should clone cleanly by default.
    if ($parent_type !== 'node') {
      logMessage("Not processing parent_type $parent_type, field $parent_field_name...");
      continue;
    }

    $rows = getMultiplyParentedParagraphFieldRows($parent_type, $parent_field_name);
    foreach ($rows as $target_id => $row) {
      $parent_ids = $row->parent_ids;
      $parent_count = count($parent_ids);

      if ($parent_count > 0) {
        $parent_list = implode(', ', $parent_ids);
        logMessage("Paragraph #{$target_id} has {$parent_count} parents: {$parent_list}");
        // We can leave this paragraph with its original parent,
        // but we wanna give clones to its other parents.
        $cloned_parent_ids = array_slice($parent_ids, 0, 1);
        if (!empty($cloned_parent_ids)) {
          repairParagraph($parent_type, $parent_field_name, (int) $cloned_parent_ids[0], (int) $target_id);
        }
      }
    }

  }
}

run();
