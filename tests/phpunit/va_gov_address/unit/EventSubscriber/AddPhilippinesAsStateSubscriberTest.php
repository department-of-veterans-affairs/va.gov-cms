<?php

namespace tests\phpunit\va_gov_address\unit\EventSubscriber;

use Drupal\address\Event\AddressEvents;
use Drupal\address\Event\SubdivisionsEvent;
use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_address\EventSubscriber\AddPhilippinesAsStateSubscriber;
use Prophecy\Argument;

/**
 * Tests custom US address group.
 *
 * @coversDefaultClass \Drupal\va_gov_address\EventSubscriber\AddPhilippinesAsStateSubscriber
 *
 * @group va_gov_address
 */
class AddPhilippinesAsStateSubscriberTest extends UnitTestCase {

  /**
   * The event subscriber under test.
   *
   * @var \Drupal\va_gov_address\EventSubscriber\AddPhilippinesAsStateSubscriber
   */
  protected AddPhilippinesAsStateSubscriber $subscriber;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->subscriber = new AddPhilippinesAsStateSubscriber();
  }

  /**
   * Tests the getSubscribedEvents method.
   *
   * @covers ::getSubscribedEvents
   */
  public function testGetSubscribedEvents() {
    $events = AddPhilippinesAsStateSubscriber::getSubscribedEvents();
    $this->assertArrayHasKey(AddressEvents::SUBDIVISIONS, $events);
    $this->assertIsArray($events[AddressEvents::SUBDIVISIONS]);
    $this->assertEquals(['onSubdivisions'], array_column($events[AddressEvents::SUBDIVISIONS], 0));
  }

  /**
   * Tests the onSubdivisions method.
   *
   * @covers ::onSubdivisions
   */
  public function testOnSubdivisions() {
    // Create a mock event.
    /** @var \Prophecy\Prophecy\ObjectProphecy|\Drupal\address\Event\SubdivisionsEvent $event */
    $event = $this->prophesize(SubdivisionsEvent::class);

    // Configure the prophecy to return specific values for getDefinitions().
    $definitions = [
      'subdivisions' => [
        'CA' => [
          'code' => 'CA',
          'name' => 'California',
          'country_code' => 'US',
          'id' => 'CA',
        ],
      ],
    ];
    $event->getDefinitions()->willReturn($definitions);

    // Set up expected calls for getParents() and setDefinitions().
    $event->getParents()->willReturn(['US']);
    $event->setDefinitions(Argument::that(function ($definitions) {
      // Validate that Philippines (PH) is added as a subdivision.
      return isset($definitions['subdivisions']['PH'])
        && $definitions['subdivisions']['PH']['name'] === 'Philippines';
    }))->shouldBeCalled();

    // Call the onSubdivisions method.
    $this->subscriber->onSubdivisions($event->reveal());
  }

  /**
   * Tests that onSubdivisions does not modify definitions for non-US parents.
   *
   * @covers ::onSubdivisions
   */
  public function testOnSubdivisionsNonUs() {
    // Create a mock event.
    /** @var \Prophecy\Prophecy\ObjectProphecy|\Drupal\address\Event\SubdivisionsEvent $event */
    $event = $this->prophesize(SubdivisionsEvent::class);

    // Mock the getParents method to return a non-US parent.
    $event->getParents()->willReturn(['CA']);

    // Assert that setDefinitions is never called.
    $event->setDefinitions()->shouldNotBeCalled();

    // Call the onSubdivisions method.
    $this->subscriber->onSubdivisions($event->reveal());
  }

}
