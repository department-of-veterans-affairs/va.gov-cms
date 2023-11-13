<?php

namespace tests\phpunit\va_gov_content_release\unit\Request;

use Drupal\advancedqueue\Entity\QueueInterface;
use Drupal\advancedqueue\Job;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\va_gov_content_release\Exception\RequestException;
use Drupal\va_gov_content_release\Request\Request;
use Drupal\va_gov_content_release\Request\RequestInterface;
use Prophecy\Argument;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test of the Request service.
 *
 * @group unit
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\Request\Request
 */
class RequestTest extends VaGovUnitTestBase {

  /**
   * Verify that submitting a request works.
   *
   * @covers ::submitRequest
   */
  public function testSubmitRequest() {
    $queueProphecy = $this->prophesize(QueueInterface::class);
    $queueProphecy->enqueueJob(Argument::type(Job::class))->willReturn(TRUE)->shouldBeCalled();
    $queue = $queueProphecy->reveal();
    $queueStorageProphecy = $this->prophesize(EntityStorageInterface::class);
    $queueStorageProphecy->load(RequestInterface::QUEUE_NAME)->willReturn($queue);
    $queueStorage = $queueStorageProphecy->reveal();
    $entityTypeManagerProphecy = $this->prophesize(EntityTypeManagerInterface::class);
    $entityTypeManagerProphecy->getStorage('advancedqueue_queue')->willReturn($queueStorage);
    $entityTypeManager = $entityTypeManagerProphecy->reveal();
    $request = new Request($entityTypeManager);
    $request->submitRequest("test");
  }

  /**
   * Verify that an exception is propagated.
   *
   * @covers ::submitRequest
   */
  public function testSubmitRequestException() {
    $queueProphecy = $this->prophesize(QueueInterface::class);
    $queueProphecy->enqueueJob(Argument::type(Job::class))->willThrow(new \Exception('test'))->shouldBeCalled();
    $queue = $queueProphecy->reveal();
    $queueStorageProphecy = $this->prophesize(EntityStorageInterface::class);
    $queueStorageProphecy->load(RequestInterface::QUEUE_NAME)->willReturn($queue);
    $queueStorage = $queueStorageProphecy->reveal();
    $entityTypeManagerProphecy = $this->prophesize(EntityTypeManagerInterface::class);
    $entityTypeManagerProphecy->getStorage('advancedqueue_queue')->willReturn($queueStorage);
    $entityTypeManager = $entityTypeManagerProphecy->reveal();
    $request = new Request($entityTypeManager);
    $this->expectException(RequestException::class);
    $request->submitRequest("test");
  }

}
