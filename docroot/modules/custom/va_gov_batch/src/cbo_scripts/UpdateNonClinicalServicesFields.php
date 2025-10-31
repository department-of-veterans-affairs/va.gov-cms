<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;

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
    $fields_to_check = [
      'field_office_visits',
      'field_virtual_support',
      'field_appt_intro_text_type',
      'field_appt_intro_text_custom',
      'field_use_facility_phone_number',
      'field_other_phone_numbers',
      'field_online_scheduling_avail',
    ];
    try {
      $storage = $this->entityTypeManager->getStorage('node');
      $query = $storage->getQuery();
      $query->accessCheck(FALSE)
        ->condition('type', 'vha_facility_nonclinical_service')
        ->condition('field_service_location', NULL, 'IS NOT NULL');

      $nids = $query->execute();

      foreach ($nids as $nid) {
        /** @var \Drupal\node\Entity\Node $node */
        $node = $storage->load($nid);
        /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $ref_field */
        $ref_field = $node->get('field_service_location');
        /** @var \Drupal\paragraphs\Entity\Paragraph[] $service_locations */
        $service_locations = $ref_field->referencedEntities();

        foreach ($service_locations as $service_location) {
          $needs_update = FALSE;
          foreach ($fields_to_check as $field) {
            if ($service_location->hasField($field) && !$service_location->get($field)->isEmpty()) {
              $needs_update = TRUE;
              break;
            }
          }
          if ($needs_update) {
            $items[] = $nid;
            break;
          }
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
      $storage = $this->entityTypeManager->getStorage('node');

      /** @var \Drupal\node\Entity\Node $node */
      $node = $storage->load($item);
      $node->setNewRevision();
      $node->setRevisionLogMessage("Clearing hidden service location fields");
      $node->save();

      $message = "Non-clinical service node ID $item processed successfully.";
      $this->batchOpLog->appendLog($message);
    }
    catch (\Exception $e) {
      $message = "Error processing non-clinical service node ID $item: " . $e->getMessage();
      $this->batchOpLog->appendError($message);
    }
    return "Node $item was processed.";
  }

}
