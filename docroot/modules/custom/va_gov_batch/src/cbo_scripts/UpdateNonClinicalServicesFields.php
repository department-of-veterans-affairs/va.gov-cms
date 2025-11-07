<?php

namespace Drupal\va_gov_batch\cbo_scripts;

require_once __DIR__ . '/../../../../../../scripts/content/script-library.php';

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * For VACMS-22248.
 *
 * This script with find all non-clinical service nodes that have data in the
 * hidden service location fields. It will then clear that data from those
 * fields.
 *
 * To run: drush codit-batch-operations:run UpdateNonClinicalServicesFields .
 */
class UpdateNonClinicalServicesFields extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return "Clears unneeded service location fields from non-clinical service nodes";
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@total non-clinical services updates were attempted. @completed were completed.';
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
        ->condition('type', 'vha_facility_nonclinical_service')
        ->condition('field_service_location', NULL, 'IS NOT NULL');

      $nids = $query->execute();

      foreach ($nids as $nid) {
        /** @var \Drupal\node\Entity\Node $node */
        $node = $storage->load($nid);
        $needs_update = $this->needsUpdate($node);

        if (!$needs_update) {
          // Check if there are newer revisions than default.
          $default_rev_id = $node->getRevisionId();
          $latest_revision_id = $storage->getLatestRevisionId($nid);
          if ($latest_revision_id > $default_rev_id) {
            $latest_revision = $storage->loadRevision($latest_revision_id);
            $needs_update = $this->needsUpdate($latest_revision);
          }
        }

        if ($needs_update) {
          $items[] = $nid;
        }

      }
    }
    catch (\Exception $e) {
      $message = "Error gathering non-clinical service nodes: " . $e->getMessage();
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

      /** @var \Drupal\node\Entity\Node $node */
      $node = $storage->load($item);
      $revision_message = "Clearing hidden service location fields";

      $default_rev_id = $node->getRevisionId();
      $latest_revision_id = $storage->getLatestRevisionId($item);

      $rev_batch_log_message = '';

      // Save forward revisions if they exist.
      if ($latest_revision_id > $default_rev_id) {
        /** @var \Drupal\node\NodeInterface $revision */
        $revision = $storage->loadRevision($latest_revision_id);
        $existing_message = $revision->getRevisionLogMessage() ?? '';
        save_node_revision($revision, $revision_message . ' - ' . $existing_message, FALSE);
        $rev_batch_log_message = " (also updated latest revision ID:$latest_revision_id for this item.)";
      }

      save_node_revision($node, $revision_message, TRUE);

      $message = "Non-clinical service node ID $item processed successfully." . $rev_batch_log_message;
      $this->batchOpLog->appendLog($message);
    }
    catch (\Exception $e) {
      $message = "Error processing non-clinical service node ID $item: " . $e->getMessage();
      $this->batchOpLog->appendError($message);
    }
    return "Node $item was processed.";
  }

  /**
   * Checks if data in fields needs to be cleared.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to check.
   *
   * @return bool
   *   Whether an update is needed.
   */
  protected function needsUpdate(EntityInterface $entity): bool {
    $fields_to_check = [
      'field_office_visits',
      'field_virtual_support',
      'field_appt_intro_text_type',
      'field_appt_intro_text_custom',
      'field_use_facility_phone_number',
      'field_other_phone_numbers',
      'field_online_scheduling_avail',
    ];
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $ref_field */
    $ref_field = $entity->get('field_service_location');
    /** @var \Drupal\paragraphs\Entity\Paragraph[] $service_locations */
    $service_locations = $ref_field->referencedEntities();

    foreach ($service_locations as $service_location) {
      foreach ($fields_to_check as $field) {
        if ($service_location->hasField($field) && !$service_location->get($field)->isEmpty()) {
          return TRUE;
        }
      }
    }
    return FALSE;

  }

}
