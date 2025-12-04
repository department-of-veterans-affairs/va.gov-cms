<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\entity_clone\EntityClone\EntityCloneInterface;
use Drupal\entity_clone\EntityClone\Content\ContentEntityCloneBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Find and dedupe paragraphs with multi parents.
 */
class DedupeParagraphs extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return <<<TITLE
    For:
      - VACMS-22602: https://github.com/department-of-veterans-affairs/va.gov-cms/issues/22602.
    TITLE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return <<<ENDHERE
    Finds all paragraphs associated with multiple nodes, clones them for each node,
    sets the new paragraph for every revision, then deletes the original.
    ENDHERE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return 'Paragraph de-duping complete.';
  }

  /**
   * {@inheritDoc}
   */
  public function getItemType(): string {
    return 'paragraph parent type and field';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    $database = \Drupal::database();
    $query = $database->select('paragraphs_item_field_data', 'p');
    $query->addField('p', 'parent_type', 'parent_type');
    $query->addField('p', 'parent_field_name', 'parent_field_name');
    $result = $query->distinct()->execute()->fetchAll();
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    $parent_type = $item->parent_type;
    $parent_field_name = $item->parent_field_name;
    // We only care about cloned nodes right now, because nested paragraphs
    // should clone cleanly by default.
    if ($parent_type !== 'node') {
      $message = 'Not processing parent_type ' . $parent_type . ', field ' . $parent_field_name;
      $this->batchOpLog->appendLog($message);
      return $message;
    }
    $rows = $this->getMultiParentedParagraphFieldRows($parent_type, $parent_field_name);
    if (count($rows) > 0) {
      foreach ($rows as $target_id => $row) {
        $parent_ids = $row->parent_ids;
        $parent_count = count($parent_ids);
        if ($parent_count > 1) {
          $parent_list = implode(', ', $parent_ids);
          $message = 'Paragraph #' . $target_id . ' has ' . $parent_count . ' parents: ' . $parent_list;
          $this->batchOpLog->appendLog($message);
          $this->processParagraph($parent_type, $parent_field_name, $target_id, $parent_ids);
        }
      }
    }
    else {
      return 'No multiple parented paragraphs found for ' . $parent_type . '-> field: ' . $parent_field_name;
    }

    return 'Finished processing parent_type ' . $parent_type . ', field ' . $parent_field_name;
  }

  /**
   * Retrieve multi-parented paragraph fields with parent type and field.
   *
   * Given a parent type and parent field name,
   * we should fetch all of the paragraphs entities
   * whose revisions do not share a common parent_id.
   *
   * If a paragraph's parent_id changes from revision to revision,
   * it may be the result of an improper clone.
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
  protected function getMultiParentedParagraphFieldRows(string $parent_type, string $parent_field_name): array {
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
        $result[$target_id] = (object) [
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
    return $result;
  }

  /**
   * Process an improperly cloned paragraph.
   *
   * @param string $parent_type
   *   The type of entity parenting this paragraph.
   * @param string $parent_field_name
   *   The machine name of the parent entity field.
   * @param int $target_id
   *   The paragraph entity ID.
   * @param int[] $parent_ids
   *   The parents with improper references to this paragraph.
   */
  protected function processParagraph(string $parent_type, string $parent_field_name, int $target_id, array $parent_ids): void {
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
      $message = 'attempt to replace old paragraph #' . $target_id . ' for node #' . $parent_id . '.';
      $this->batchOpLog->appendLog($message);
      $this->replaceParagraph($cloner, $parent, $parent_field_name, $target_id, $properties, $already_cloned);
    }
    // Clean up now orphaned paragraphs.
    $paragraph_storage = \Drupal::entityTypeManager()->getStorage('paragraph');
    /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    $paragraph = $paragraph_storage->load($target_id);
    if ($paragraph) {
      $paragraph_parent_id = $paragraph->get('parent_id')->value;
      /** @var \Drupal\node\NodeInterface $parent */
      $parent = $paragraph_storage->load($paragraph_parent_id);
      if ($parent) {
        $parent_field_items = $parent->get($parent_field_name)->getValue();
        $referenced_ids = array_column($parent_field_items, 'target_id');
        if (in_array($target_id, $referenced_ids)) {
          $message = 'Paragraph #' . $target_id . ' is still referenced by node #' . $paragraph_parent_id . '.';
          $this->batchOpLog->appendLog($message);
        }
        else {
          $paragraph->delete();
        }
      }
    }
  }

  /**
   * Replace a paragraph with its clone.
   *
   * @param \Drupal\entity_clone\EntityClone\EntityCloneInterface $cloner
   *   A cloner object.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The host entity.
   * @param string $field_name
   *   The name of the paragraph field.
   * @param int $pid
   *   The paragraph entity ID to clone and replace.
   * @param array $properties
   *   Properties used to create the clone paragraph.
   * @param array &$already_cloned
   *   Tracking of already cloned paragraph IDs.
   */
  protected function replaceParagraph(EntityCloneInterface $cloner, ContentEntityInterface $entity, string $field_name, int $pid, array $properties, array &$already_cloned = []): void {
    if ($entity->hasField($field_name)) {
      $entity_type_manager = \Drupal::entityTypeManager();
      /** @var \Drupal\node\NodeStorageInterface $storage */
      $storage = $entity_type_manager->getStorage($entity->getEntityTypeId());
      /** @var \Drupal\Core\Field\EntityReferenceFieldItemList  $field_value */
      $field_value = $entity->get($field_name);
      $field_items = $field_value->getValue();
      $referenced_entities = $field_value->referencedEntities();
      $revision_ids = $storage->revisionIds($entity);
      $database = \Drupal::database();
      $replace_count = 0;
      foreach ($field_items as $key => $field_item) {
        if (intval($field_item['target_id']) === $pid) {
          $referenced_entity = $referenced_entities[$key] ?? NULL;
          if ($referenced_entity) {
            // Avoid cloning the same paragraph multiple times.
            if (!isset($already_cloned[$pid])) {
              $clone = $this->cloneParagraph($cloner, $referenced_entity, $entity->id(), $properties);
              $already_cloned[$pid] = $clone;
            }
            else {
              $clone = $already_cloned[$pid];
            }
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
            $title = method_exists($entity, 'getTitle') ? $entity->getTitle() : '';
            $message = 'Updated ' . $entity->bundle() . ' node #' . $entity->id() . ' - "' . $title . '" value #' . $key . ' with new paragraph (was ' . $pid . ', now ' . $clone->id() . ').';
            $this->batchOpLog->appendLog($message);
            $replace_count++;
            break;
          }
        }
      }
      if ($replace_count === 0) {
        $message = 'no replacement for orphaned paragraph #' . $pid . ' for node #' . $entity->id();
        $this->batchOpLog->appendLog($message);
      }
    }
  }

  /**
   * Clone a paragraph and prepare it for insertion into the original node.
   *
   * @param \Drupal\entity_clone\EntityClone\EntityCloneInterface $cloner
   *   A cloner object.
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph to clone.
   * @param int $parent_id
   *   The parent ID, used to strip unwanted revisions.
   * @param array $properties
   *   Properties used to create the clone paragraph.
   *
   * @return \Drupal\paragraphs\ParagraphInterface
   *   The cloned paragraph.
   */
  protected function cloneParagraph(EntityCloneInterface $cloner, ParagraphInterface $paragraph, int $parent_id, array $properties): ParagraphInterface {
    $clone = $paragraph->createDuplicate();
    /** @var \Drupal\paragraphs\Entity\Paragraph $result */
    $result = $cloner->cloneEntity($paragraph, $clone, $properties);
    $cloned_parent_id = $result->get('parent_id')->value;
    // Make sure that the new paragraph targets the correct parent.
    if ((string) $cloned_parent_id !== (string) $parent_id) {
      $result->set('parent_id', (string) $parent_id);
      $result->save();
    }
    return $result;
  }

}
