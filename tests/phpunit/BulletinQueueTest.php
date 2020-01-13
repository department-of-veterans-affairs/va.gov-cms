<?php

namespace tests\phpunit;

use Drupal\paragraphs\Entity\Paragraph;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm that alerts and situation updates are queued.
 */
class BulletinQueueTest extends ExistingSiteBase {

  /**
   * Runs the test to check that alerts and updates are queued.
   */
  public function testBulletinQueue() {
    // Wipe the queue clean so we can get reliable counts.
    $this->deleteQueue();

    // Save an unpublished node that should not create a bulletin.
    $node = $this->createNode(['type' => 'full_width_banner_alert']);
    $node->set('title', 'PHPUnit Test Alert');
    $node->set('uid', '1');
    $node->set('field_operating_status_sendemail', '1');
    $node->set('field_alert_type', 'warning');
    $node->set('field_body', 'This is a test created by phpUnit.  Please disregard.');
    $node->field_banner_alert_vamcs->target_id = 1010;
    $node->setUnpublished();
    $node->save();

    // Validate that there is no bulletin in the queue.
    $number_of_queue = $this->getQueueCount();
    $message = "\nThere should be no bulletin from unpublished nodes in the govdelivery_bulletin queue.\n";
    $this->assertEquals(0, $number_of_queue, $message);
    // Add a situation update to the unpublished node.
    $this->createSituationUpdate($node);
    $node->save();

    // Validate that there is no bulletin in the queue.
    $number_of_queue = $this->getQueueCount();
    $message = "\nThere should be no bulletin from unpublished situation updates in the govdelivery_bulletin queue.\n";
    $this->assertEquals(0, $number_of_queue, $message);

    // Save a node that would created a bulletin.
    unset($node);
    $node = $this->createNode(['type' => 'full_width_banner_alert']);
    $node->set('title', 'PHPUnit Test Alert');
    $node->set('uid', '1');
    $node->set('field_operating_status_sendemail', '1');
    $node->set('field_alert_type', 'warning');
    $node->set('field_body', 'This is a test created by phpUnit.  Please disregard.');
    $node->field_banner_alert_vamcs->target_id = 1010;
    $node->setPublished();
    $node->save();

    // Validate that there is a bulletin in the queue.
    $number_of_queue = $this->getQueueCount();
    $message = "\nThere should only be one alert item in the govdelivery_bulletin queue.\n";
    $this->assertEquals(1, $number_of_queue, $message);

    // Edit the node, add several situation updates to field_situation_updates.
    for ($x = 0; $x <= 5; $x++) {
      $this->createSituationUpdate($node);
      $node->save();
    }

    // Validate that there are 1 items in the queue.
    // Multiple situation updates are deduped.
    $number_of_queue = $this->getQueueCount();
    $message = "\nThere should only be one situation update per alert govdelivery_bulletin queue.\n";
    $this->assertEquals(1, $number_of_queue, $message);
  }

  /**
   * Get the freshest queue count and wipe the queue in case assertion fails.
   *
   * @return int
   *   The number of govdelivery bulletin items in the queue.
   */
  private function getQueueCount() {
    $this->refreshQueue();
    // Get the number of items.
    $number_of_queue = $this->queue->numberOfItems();
    // Wipe things clean so tests are not sitting in the queue later.
    $this->deleteQueue();
    return $number_of_queue;
  }

  /**
   * Removes the old $this->queue and gathers a new queue.
   */
  private function refreshQueue() {
    if (!empty($this->queue)) {
      unset($this->queue);
    }
    $this->queue = $queue = $this->getQueueFactory()->get('govdelivery_bulletins');
  }

  /**
   * Returns the queue factory object and initializes it if does not exist.
   *
   * @return Drupal\Core\Queue\QueueFactory
   *   The QueueFactory.
   */
  private function getQueueFactory() {
    if (empty($this->queueFactory)) {
      $this->queueFactory = \Drupal::service('queue');
    }
    return $this->queueFactory;
  }

  /**
   * Deletes any existing queue items.
   */
  private function deleteQueue() {
    $this->refreshQueue();
    $this->queue->deleteQueue();
  }

  /**
   * Create a situation update and add it to the node.
   */
  private function createSituationUpdate(&$node) {
    $paragraph = Paragraph::create(['type' => 'situation_update']);
    $paragraph->set('field_date_and_time', date('Y-m-d\TH:i:s', time()));
    $paragraph->set('field_send_email_to_subscribers', 1);
    $paragraph->set('field_wysiwyg', 'This is a test phpUnit situation update.  Please disregard.');
    $paragraph->save();

    // Grab any existing paragraphs from the node, and add this one.
    $current = $node->get('field_situation_updates')->getValue();
    $current[] = [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];
    $node->set('field_situation_updates', $current);
  }
}
