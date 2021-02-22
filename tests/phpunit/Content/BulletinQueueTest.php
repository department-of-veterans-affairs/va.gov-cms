<?php

namespace tests\phpunit\Content;

use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm that alerts and situation updates are queued.
 */
class BulletinQueueTest extends ExistingSiteBase {

  /**
   * Govdelivery bulletins queue.
   *
   * @var Drupal\Core\Queue\QueueInterface
   */
  protected $queue;

  /**
   * Queue service.
   *
   * @var Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Wipe the queue clean so we can get reliable counts.
   */
  protected function setUp() {
    parent::setUp();

    $this->deleteQueue();
  }

  /**
   * Runs the test to check that alerts and updates are queued.
   *
   * @dataProvider provideBulletinNodeData
   */
  public function testBulletinQueue(
    array $node_data,
    int $expected_queue_count,
    int $expected_queue_count_after_situation_updates
  ) : void {
    $node = $this->createNode($node_data);
    $node->save();
    $this->assertEquals($expected_queue_count, $this->getQueueCount());

    // Edit the node, add several situation updates to field_situation_updates.
    // This confirms that multiple situation updates are deduped.
    for ($x = 0; $x <= 5; $x++) {
      $this->createSituationUpdate($node);
      $node->save();
    }
    $this->assertEquals($expected_queue_count_after_situation_updates, $this->getQueueCount());
  }

  /**
   * Data provider for testBulletinQueue.
   *
   * @return \Generator
   *   Test assertion data.
   */
  public function provideBulletinNodeData() : \Generator {
    $node_data_template = [
      'type' => 'full_width_banner_alert',
      'title' => 'PHPUnit Test Alert',
      'uid' => '1',
      'field_operating_status_sendemail' => '1',
      'field_alert_type' => 'warning',
      'field_body' => 'This is a test created by phpUnit.  Please disregard.',
      'field_banner_alert_vamcs' => [
        'target_id' => 1010,
      ],
      'status' => 1,
    ];

    $node_data = $node_data_template;
    $node_data['status'] = 0;
    yield 'There should be no bulletin from unpublished nodes in the govdelivery_bulletin queue.' => [
      $node_data,
      0,
      0,
    ];

    $node_data = $node_data_template;
    $node_data['field_operating_status_sendemail'] = 0;
    yield 'There should be no bulletin from published nodes with the "Send update" field unchecked in the govdelivery_bulletin queue.' => [
      $node_data,
      0,
      0,
    ];

    yield 'There should be a bulletin from published nodes with the "Send update" field checked in the govdelivery_bulletin queue.' => [
      $node_data_template,
      1,
      1,
    ];
  }

  /**
   * Get the freshest queue count and wipe the queue in case assertion fails.
   *
   * @return int
   *   The number of govdelivery bulletin items in the queue.
   */
  private function getQueueCount() : int {
    $this->refreshQueue();

    $number_of_queue = $this->queue->numberOfItems();

    // Wipe things clean so tests are not sitting in the queue later.
    $this->deleteQueue();
    return $number_of_queue;
  }

  /**
   * Removes the old $this->queue and gathers a new queue.
   */
  private function refreshQueue() : void {
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
  private function deleteQueue() : void {
    $this->refreshQueue();
    $this->queue->deleteQueue();
  }

  /**
   * Create a situation update and add it to the node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to which to add a situation update paragraph.
   */
  private function createSituationUpdate(NodeInterface &$node) : void {
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
