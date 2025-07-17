<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * For VACMS-21680.
 *
 * @see https://github.com/department-of-veterans-affairs/va.gov-cms/issues/21680
 */
class ArchivePastEvents extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle():string {
    return "Archive past events";
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription():string {
    return <<<ENDHERE
    Archive all events that at least 30 days old.
    ENDHERE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@total menu item updates were attempted. @completed were completed.';
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
    // Get all past events.
    $query = \Drupal::entityQuery('node')
      ->accessCheck(FALSE)
      ->condition('type', 'event')
      ->condition('field_event_date', strtotime('-30 days'), '<')
      ->condition('status', 1); // Only published events.
    $event_ids = $query->execute();

    return $event_ids;

  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    try {
      // Load the event node.
      $event = \Drupal\node\Entity\Node::load($item);
      if (!$event) {
        $this->batchOpLog->appendError("Could not load the event node with ID $item");
        return "Item $item was not processed.";
      }
      // Set the user ID to the Migration user.
      $event->setRevisionUserId(1317);
      // Set the event to archived.
      $event->set('moderation_state', 'archived');
      $event->save();
      $message = "Event $item archived successfully.";
      $this->batchOpLog->appendLog($message);
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
      $this->batchOpLog->appendError("Could not archive the event. The error is $message");
    }
    return "Item $item was processed.";

  }

}
