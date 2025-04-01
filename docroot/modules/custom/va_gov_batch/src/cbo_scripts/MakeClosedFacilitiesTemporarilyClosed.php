<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;

/**
 * @file
 * Updates facilities from Closed to Temporary facility closure status.
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
    Updates facilities from "Closed" status to "Temporary facility closure" operating status.
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
        $message = "Updated operating status from Closed to Temporary facility closure via script.";
        $this->saveNodeRevision($revision, $message);
      }
      return "Updated node $item Operating Status from Closed to Temporary facility closure status.";
    }
    catch (\Exception $e) {
      $message = "Exception during update of node $item: $e->getMessage()";
      return $message;
    }
  }

}
