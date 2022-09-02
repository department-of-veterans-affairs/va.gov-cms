<?php

namespace tests\phpunit\FrontendBuild;

use Drupal\advancedqueue\Entity\QueueInterface;
use Drupal\advancedqueue\Job;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\KeyValueStore\KeyValueMemoryFactory;
use Drupal\Core\State\State;
use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_build_trigger\Service\BuildRequester;

/**
 * Unit test for the build requester..
 *
 * @group unit
 * @group all
 */
class BuildRequesterTest extends UnitTestCase {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->state = new State(new KeyValueMemoryFactory());
  }

  /**
   * Test that we're getting state values back from getState().
   */
  public function testRequestFrontendBuild() {
    // Mock the queue and assert that we're getting the right things in the job
    // that is created and enqueued.
    $queue = $this->getMockBuilder(QueueInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $queue->expects($this->once())
      ->method('enqueueJob')
      ->with(
        $this->callback(function (Job $job) {
          $this->assertInstanceOf(Job::class, $job);
          $this->assertEquals('va_gov_content_release_request', $job->getType());

          $payload = $job->getPayload();
          $this->assertNotNull($payload);
          $this->assertArrayHasKey('reason', $payload);
          $this->assertEquals('TEST REASON', $payload['reason']);

          // We're abusing the ->callback() method here a little bit to ensure
          // that the _contents_ of the job are correct. Returning true just
          // tells PHPunit that the argument was acceptable.
          return TRUE;
        }),
      );

    $entityTypeManager = $this->getEntityTypeManagerWithStorageAndQueue($queue);

    // Finally, create a build requester and request a build (which should
    // trigger the assertions above).
    $buildRequester = new BuildRequester($entityTypeManager, $this->state);
    $buildRequester->requestFrontendBuild('TEST REASON');
  }

  /**
   * Test that setting and unsetting the frontend version works.
   */
  public function testFrontendVersionSelection() {
    $queue = $this->getMockBuilder(QueueInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityTypeManager = $this->getEntityTypeManagerWithStorageAndQueue($queue);

    $buildRequester = new BuildRequester($entityTypeManager, $this->state);

    $buildRequester->switchFrontendVersion('foobar');
    $this->assertEquals('foobar', $this->state->get(BuildRequester::VA_GOV_FRONTEND_VERSION));

    $buildRequester->resetFrontendVersion();
    $this->assertEquals('NOT PRESENT', $this->state->get(BuildRequester::VA_GOV_FRONTEND_VERSION, 'NOT PRESENT'));
  }

  /**
   * Get an entity type manager that can eventually return a queue.
   *
   * @param \Drupal\advancedqueue\Entity\QueueInterface $queue
   *   A queue (or a mock).
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   A mocked EntityStorageInterface.
   */
  protected function getEntityTypeManagerWithStorageAndQueue(QueueInterface $queue) {
    // Build an entity storage manager to return the queue.
    $entityStorageManager = $this->getMockBuilder(EntityStorageInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityStorageManager->method('load')
      ->with('content_release')
      ->willReturn($queue);

    // Build an entity type manager to return the storage manager.
    $entityTypeManager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityTypeManager->method('getStorage')
      ->with('advancedqueue_queue')
      ->willReturn($entityStorageManager);

    return $entityTypeManager;
  }

}
