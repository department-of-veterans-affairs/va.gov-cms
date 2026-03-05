<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;

/**
 * For VACMS-19212.
 *
 * This script will copy the value from the title field to field_official_name
 * for all VBA Facility nodes. This is necessary to support condensed names
 * being used in the title field, while preserving the full facility name in the
 * official name field.
 *
 * To run: drush codit-batch-operations:run VBACopyTitleToOfficialName.
 */
class VBACopyTitleToOfficialName extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return "Copy VBA Facility title to official name field";
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@total VBA Facility nodes were processed. @completed were updated with title copied to official name field.';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    $items = [];
    try {
      /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
      $storage = $this->entityTypeManager->getStorage('node');
      $query = $storage->getQuery();
      $query->accessCheck(FALSE)
        ->condition('type', 'vba_facility')
        ->condition('field_official_name', NULL, 'IS NULL');

      $items = $query->execute();
    }
    catch (\Exception $e) {
      $message = "Error gathering VBA Facility nodes: " . $e->getMessage();
      $this->batchOpLog->appendError($message);
    }

    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    try {
      /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
      $storage = $this->entityTypeManager->getStorage('node');
      $revision_message = "Copying title to official name field.";

      /** @var \Drupal\node\Entity\Node $node */
      $node = $storage->load($item);
      $title = $node->getTitle();
      $node->set('field_official_name', $title);
      $this->saveNodeRevision($node, $revision_message);
    }
    catch (\Exception $e) {
      $message = "Error processing VBA Facility node with ID $item: " . $e->getMessage();
      $this->batchOpLog->appendError($message);
    }

    return "VBA Facility node $item was processed.";
  }

}
