<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\node\Entity\Node;

/**
 * For VACMS-21680.
 *
 * @see https://github.com/department-of-veterans-affairs/va.gov-cms/issues/21680
 */
class ArchivePastEvents extends BatchOperations implements BatchScriptInterface {

  /**
   * The user ID to set for the migration.
   */
  const MIGRATION_USER_ID = 1317;

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return "Archive past events";
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return <<<ENDHERE
    Archive all events that are at least 30 days old.
    ENDHERE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@total event updates were attempted. @completed were completed.';
  }

  /**
   * {@inheritdoc}
   */
  public function getItemType(): string {
    return 'event';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    // Get all published events.
    $query = \Drupal::entityQuery('node')
      ->accessCheck(FALSE)
      ->condition('type', 'event')
      ->condition('status', 1);
    $event_ids = $query->execute();

    $thirty_days_ago = strtotime('-30 days');
    $event_ids_to_archive = [];

    if (!empty($event_ids)) {
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $nodes = $node_storage->loadMultiple($event_ids);
      foreach ($nodes as $node) {
        // Smart Date stores end_value as a property on the field item.
        $date = $node->get('field_datetime_range_timezone')->getValue();
        if (!$date) {
          continue;
        }
        // Start with newest date and work backwards.
        for ($i = count($date) - 1; $i >= 0; $i--) {
          if (isset($date[$i]['end_value'])) {
            $end_value = $date[$i]['end_value'];
            if ($end_value === null || $end_value === '') {
              continue;
            }
            // Check that the event's end date is older than 30 days.
            if ($end_value < $thirty_days_ago) {
              $event_ids_to_archive[] = $node->id();
              // We only need to add an event once, so break.
              break;
            }
            else {
              // No need to check earlier dates.
              break;
            }
          }
        }
      }
    }

    return $event_ids_to_archive;
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    try {
      // Load the event node.
      $event = Node::load($item);

      if (!$event) {
        $this->batchOpLog->appendError("Could not load the event node with ID $item");
        return "Item $item was not processed.";
      }
      // Set the user ID to the Migration user.
      $event->setRevisionUserId(self::MIGRATION_USER_ID);
      // Set the event to archived.
      $event->set('moderation_state', 'archived');
      $event->set('revision_log', 'Archived via batch operation because it is older than 30 days.');
      $event->save();
      $message = "Event $item archived successfully.";
      $this->batchOpLog->appendLog($message);
    }
    catch (EntityStorageException $e) {
      $message = $e->getMessage();
      $this->batchOpLog->appendError("Could not archive the event. The error is $message");
    }
    return "Item $item was processed.";
  }

}
