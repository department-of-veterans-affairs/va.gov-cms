<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;

/**
 * @file
 * For non-numerical characters in the phone_number paragraph extension field.
 *
 * For VACMS-20738.
 * drush codit-batch-operations:run MakeClosedFacilitiesTemporarilyClosed .
 */
/**
 * Makes Closed facilities have Temporary Closure status.
 */
class MakeClosedFacilitiesTemporarilyClosed extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return <<<TITLE
    For:
      - VACMS-20738: https://github.com/department-of-veterans-affairs/va.gov-cms/issues/20738.
    TITLE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription():string {
    return <<<ENDHERE
    Updates facilities with closed status to have Temporary Closure status.
    ENDHERE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return 'Facilities with "Closed" operating status have been updated with @total processed and @completed completed.';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    return \Drupal::database()
      ->select('node__field_operating_status_facility', 'nfosf')
      ->fields('nfosf', ['entity_id'])
      ->condition('field_operating_status_facility_value', 'closed', '=')
      ->execute()
      ->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    try {
      $node_revisions = $this->getNodeDefaultAndForwardRevisions($item);
      foreach ($node_revisions as $revision) {
        $revision->set('field_operating_status_facility', 'temporary_closure');
        $message = "Updated from Closed to Temporary Closure via script.";
        $this->saveNodeRevision($revision, $message);
      }
      return "Updated node $item with Temporary Closure status.";
    }
    catch (\Exception $e) {
      $message = "Exception during update of node $item: $e";
      return $message;
    }
  }

}
