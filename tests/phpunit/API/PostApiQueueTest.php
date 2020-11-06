<?php

namespace tests\phpunit\API;

use Drupal\Core\Database\Database;
use Drupal\Core\Queue\DatabaseQueue;
use Drupal\Core\Site\Settings;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Tests Post API Queue functionality.
 *
 * @group PostApiQueue
 */
class PostApiQueueTest extends ExistingSiteBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['post_api'];

  /**
   * Mock queue item data.
   *
   * @var array
   */
  public static $mockData = [
    'nid' => 1546,
    'uid' => 'facility_status_vha_568A4',
    'endpoint_path' => '/services/va_facilities/v0/facilities/vha_568A4/cms-overlay',
    'payload' => [
      'operating_status' => [
        'code' => 'CLOSED',
        'additional_info' => 'Test additional info',
      ],
    ],
  ];

  /**
   * Setup.
   */
  public function setUp() {
    parent::setUp();
    $queue = new DatabaseQueue('post_api_queue', Database::getConnection());
    $queue->deleteQueue();
  }

  /**
   * Tests queue item expiration.
   *
   * NOTE: Core has a bug, where claimItem in the database and memory queues
   * does not use expire correctly.
   * Expiration test depend on applied core patch
   * https://www.drupal.org/project/drupal/issues/2893933#comment-13413895
   * If patch is not applied, this test will fail.
   */
  public function testPostApiQueueItemExpiration() {
    $queue = $this->createQueue();

    // Test that we can claim an item that is expired and we cannot claim an
    // item that has not expired yet.
    $queue->createItem([$this->randomMachineName() => $this->randomMachineName()]);
    $item = $queue->claimItem();
    $this->assertNotFalse($item, 'The item can be claimed.');
    $item = $queue->claimItem();
    $this->assertFalse($item, 'The item cannot be claimed again.');
    // Set the expiration date to the current time minus the lease time plus 1
    // second. It should be possible to reclaim the item.
    $this->setExpiration($queue, time() - 31);
    $item = $queue->claimItem();
    $this->assertNotFalse($item, 'Item can be claimed after expiration.');

    $queue->deleteQueue();
  }

  /**
   * Processes mock queue items to test Lighthouse API responses.
   */
  public function testPostApiQueueProcessing() {
    // Create queue.
    $queue = $this->createQueue();

    $response = $this->processItem(NULL);
    // Payload is empty. Request should fail.
    $this->assertEquals(404, $response, 'POST request is expected to fail with 404 due to incomplete endpoint URL.');

    $response = $this->processItem(self::$mockData);
    // Payload and credentials are available. POSt should return 200 OK.
    $this->assertEquals(200, $response, 'POST request is successful.');

    $queue->deleteQueue();
  }

  /**
   * Queues items and verifies that dedupe method works as expected.
   */
  public function testPostApiQueueDedupe() {
    // Create queue.
    $queue = $this->createQueue();

    // Check that queue is empty.
    $this->assertSame(0, $queue->numberOfItems(), 'ERROR: Post API Queue is not empty');

    // Dedupe test.
    $add_to_queue_service = \Drupal::service('post_api.add_to_queue');
    // Dedupe while adding.
    $add_to_queue_service->addToQueue(self::$mockData, TRUE);
    $add_to_queue_service->addToQueue(self::$mockData, TRUE);

    $this->assertSame(1, $queue->numberOfItems(), 'ERROR: Post API Queue contains duplicate items.');

    $queue->deleteQueue();
  }

  /**
   * Create Queue.
   *
   * @return \Drupal\core\Queue\DatabaseQueue
   *   Queue.
   */
  protected function createQueue() {
    // Create queue.
    $queue = new DatabaseQueue('post_api_queue', Database::getConnection());
    $queue->createQueue();
    return $queue;
  }

  /**
   * Set the expiration for different queues.
   *
   * @param object $queue
   *   The queue for which to alter the expiration.
   * @param int $expire
   *   The new expiration time.
   */
  protected function setExpiration($queue, $expire) {
    $class = get_class($queue);
    switch ($class) {
      case DatabaseQueue::class:
        \Drupal::database()
          ->update(DatabaseQueue::TABLE_NAME)
          ->fields(['expire' => $expire])
          ->execute();
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function processItem($data) {
    $apikey = Settings::get('post_api_apikey');
    $endpoint_path = isset($data['endpoint_path']) ? $data['endpoint_path'] : NULL;
    $endpoint = Settings::get('post_api_endpoint_host') . $endpoint_path;
    $payload = isset($data['payload']) ? $data['payload'] : [];

    $request_service = \Drupal::service('post_api.request');

    // Send POST request and return the response.
    return $request_service->sendRequest($endpoint, $apikey, $payload);
  }

}
