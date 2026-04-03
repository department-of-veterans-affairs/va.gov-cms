<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;

/**
 * For VACMS-23674.
 *
 * This script updates the title of all VA Form nodes that still have the old
 * "About VA Form " prefix, replacing it with the new "VA Form " prefix. This
 * is needed because the migration previously overwrote the title on every run
 * and used the old prefix. Now that title is editable and no longer overwritten,
 * existing nodes need a one-time backfill to use the new prefix.
 *
 * To run: drush codit-batch-operations:run VaFormBackfillPageTitle .
 */
class VaFormBackfillPageTitle extends BatchOperations implements BatchScriptInterface {

  /**
   * The old title prefix to be replaced.
   */
  const OLD_PREFIX = 'About VA Form ';

  /**
   * The new title prefix.
   */
  const NEW_PREFIX = 'VA Form ';

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return 'Backfill VA Form node titles with new prefix';
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@total VA Form title updates were attempted. @completed were completed.';
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
        ->condition('type', 'va_form')
        ->condition('title', self::OLD_PREFIX, 'STARTS_WITH');

      $items = $query->execute();
    }
    catch (\Exception $e) {
      $message = 'Error gathering VA Form nodes: ' . $e->getMessage();
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
      $revision_message = 'Updated title prefix from "About VA Form" to "VA Form".';

      /** @var \Drupal\node\NodeInterface $node */
      $node = $storage->load($item);
      $default_rev_id = $node->getRevisionId();
      $latest_revision_id = $storage->getLatestRevisionId($item);

      $rev_batch_log_message = '';

      // Update forward revision first if one exists.
      if ($latest_revision_id > $default_rev_id) {
        /** @var \Drupal\node\NodeInterface $revision */
        $revision = $storage->loadRevision($latest_revision_id);
        $revision_title = $revision->getTitle();
        if (str_starts_with($revision_title, self::OLD_PREFIX)) {
          $new_title = self::NEW_PREFIX . substr($revision_title, strlen(self::OLD_PREFIX));
          $revision->setTitle($new_title);
          $existing_message = $revision->getRevisionLogMessage() ?? '';
          $this->saveNodeRevision($revision, $revision_message . ' - ' . $existing_message, FALSE);
          $rev_batch_log_message = " (also updated forward revision ID:$latest_revision_id)";
        }
      }

      // Update the default revision.
      $title = $node->getTitle();
      $new_title = self::NEW_PREFIX . substr($title, strlen(self::OLD_PREFIX));
      $node->setTitle($new_title);
      $this->saveNodeRevision($node, $revision_message);

      $message = "VA Form node ID $item title updated." . $rev_batch_log_message;
      $this->batchOpLog->appendLog($message);
    }
    catch (\Exception $e) {
      $message = "Error processing VA Form node ID $item: " . $e->getMessage();
      $this->batchOpLog->appendError($message);
    }

    return "VA Form node $item was processed.";
  }

}
