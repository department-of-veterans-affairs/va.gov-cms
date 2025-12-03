<?php

/**
 * @file
 * One-time migration for fixing improperly cloned paragraphs.
 *
 * Users of the entity_clone form were deselecting the checkboxes for
 * Paragraphs entities, which led to cloned nodes having references to
 * the original node's Paragraphs.
 *
 * Our approach here will be:
 *
 * 1. Retrieve a list of all paragraphs with multiple parents.
 * 2. Repair the nodes owning those paragraphs:
 *   A. Determine whether it is a cloned node; if not, no action is necessary.
 *   B. Clone the paragraph.
 *   C. Update the cloned node to refer instead to the cloned paragraph.
 *   D. Save the node.
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
  // logMessage("{$parent_type}: {$parent_field_name}... {$count} multiply-parented field rows found.");

  return $result;
}

/**
 * Clone a paragraph and prepare it for insertion into the original node.
 *
 * @param \Drupal\entity_clone\EntityClone\EntityCloneInterface $cloner
 *  A cloner object.
 * @param \Drupal\paragraphs\ParagraphInterface $paragraph
 *   The paragraph to clone.
 * @param int $parent_id
 *   The parent ID, used to strip unwanted revisions.
 * @param array $properties
 *  Properties used to create the clone paragraph.
 *
 * @return \Drupal\paragraphs\ParagraphInterface
 *   The cloned paragraph.
 */
function cloneParagraph(EntityCloneInterface $cloner, ParagraphInterface $paragraph, int $parent_id, array $properties): ParagraphInterface {
  $clone = $paragraph->createDuplicate();
  /** @var \Drupal\paragraphs\entity\Paragraph $result */
  $result = $cloner->cloneEntity($paragraph, $clone, $properties);
  $cloned_parent_id = $result->get('parent_id')->value;
  // Make sure that the new paragraph targets the correct parent.
  if ($cloned_parent_id !== (string)$parent_id) {
    $result->set('parent_id', (string)$parent_id);
    $result->save();
  }
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
 */
function replaceParagraph(EntityCloneInterface $cloner, ContentEntityInterface $entity, string $field_name, int $pid, array $properties): void {
  if ($entity->hasField($field_name)) {
    $entity_type_manager = \Drupal::entityTypeManager();
    $field_value = $entity->get($field_name);
    $field_items = $field_value->getValue();
    $referenced_entities = $field_value->referencedEntities();
    $storage = $entity_type_manager->getStorage($entity->getEntityTypeId());
    $revision_ids = $storage->revisionIds($entity);
    $database = \Drupal::database();
    foreach ($field_items as $key => $field_item) {
      if (intval($field_item['target_id']) === $pid) {
        $referenced_entity = $referenced_entities[$key];
        if ($referenced_entity) {
          $clone = cloneParagraph($cloner, $referenced_entity, $entity->id(), $properties);

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
          $query->condition('revision_id', $revision_ids, 'IN');
          $query->condition('delta', $key);
          $query->execute();

          logMessage("Updated #{$entity->bundle()} node #{$entity->id()} \"{$entity->getTitle()}\" value #{$key} with new paragraph (was {$pid}, now {$clone->id()}).");
          break;
        }
        break;
      } else {
        logMessage("no replacement for orphaned paragraph #$pid for node #{$entity->id()}.");
      }
    }
  }
}

/**
 * Process an improperly cloned paragraph.
 *
 * @param string $parent_type
 *   The type of entity parenting this paragraph.
 * @param string $parent_field_name
 *   The machine name of the parent entity.
 * @param int $id
 *   The paragraph entity ID.
 * @param int[] $parent_ids
 *   The parents with improper references to this paragraph.
 */
function processParagraph(string $parent_type, string $parent_field_name, int $target_id, array $parent_ids): void {
  $entity_type_manager = \Drupal::entityTypeManager();
  $time_service = \Drupal::service('datetime.time');
  $current_user = \Drupal::service('current_user');
  $clonable_field = \Drupal::service('entity_clone.clonable_field');
  $cloner = new ContentEntityCloneBase($entity_type_manager, $parent_type, $time_service, $current_user, $clonable_field);
  $parent_storage = $entity_type_manager->getStorage($parent_type);
  $parents = $parent_storage->loadMultiple($parent_ids);
  $already_cloned = [];
  $properties = [
    'children' => [],
  ];
  foreach ($parents as $parent_id => $parent) {
    logMessage("attempt to replace old paragraph #$target_id for node #$parent_id.");
    replaceParagraph($cloner, $parent, $parent_field_name, $target_id, $properties, $already_cloned);
  }
  // Clean up now orphaned paragraphs
  $paragraph_storage = \Drupal::entityTypeManager()->getStorage('paragraph');
  $paragraph = $paragraph_storage->load($target_id);
  if ($paragraph) {
    $paragraph->delete();
  }
}

/**
 * Delete non-default revisions of the specified nodes.
 *
 * @param int[] $nids
 *   The nodes whose revision histories should be deleted.
 */
function deleteRevisionHistoryForNodes(array $nids) {
  /** @var \Drupal\Core\Entity\EntityStorageInterface $node_storage */
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');
  /** @var \Drupal\node\NodeInterface[] $nodes */
  $nodes = $node_storage->loadMultiple($nids);
  foreach ($nodes as $node) {
    $default_revision_id = $node->getRevisionId();
    $revision_ids = array_diff($node_storage->revisionIds($node), [ $default_revision_id ]);
    foreach ($revision_ids as $revision_id) {
      logMessage("Deleting old revision #$revision_id for node #{$node->id()}.");
      $node_storage->deleteRevision($revision_id);
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

      if ($parent_count > 1) {
        $parent_list = implode(', ', $parent_ids);
        logMessage("Paragraph #{$target_id} has {$parent_count} parents: {$parent_list}");
        processParagraph($parent_type, $parent_field_name, $target_id, $parent_ids);
      }
    }

  }
}

run();
