<?php

namespace tests\phpunit\FrontendBuild;

use Drupal\Core\KeyValueStore\KeyValueMemoryFactory;
use Drupal\Core\State\State;
use Drupal\prometheus_exporter\MetricsCollectorManager;
use Drupal\Tests\UnitTestCase;
use Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent;
use Drupal\va_gov_build_trigger\EventSubscriber\ContentReleaseErrorSubscriber;
use Drupal\va_gov_build_trigger\EventSubscriber\ContentReleaseIntervalSubscriber;
use Drupal\va_gov_build_trigger\EventSubscriber\ContentReleaseMetricsRecalculationSubscriber;
use Drupal\va_gov_build_trigger\Plugin\MetricsCollector\ContentReleaseInterval;
use Drupal\va_gov_build_trigger\Service\BuildRequester;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Support\Mock\SpecifiedTime;

/**
 * Unit test for build state event subscribers.
 */
class BuildStateEventSubscriberTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->state = new State(new KeyValueMemoryFactory());
  }

  /**
   * Tests the ContentReleaseErrorSubscriber class.
   */
  public function testContentReleaseErrorSubscriber() {
    $buildRequester = $this->getMockBuilder(BuildRequester::class)
      ->disableOriginalConstructor()
      ->getMock();

    $buildRequester->expects($this->never())
      ->method('requestFrontendBuild');

    $no_states = [
      'ready',
      'requested',
      'dispatched',
      'starting',
      'inprogress',
      'complete',
    ];

    foreach ($no_states as $state) {
      $subscriber = new ContentReleaseErrorSubscriber($buildRequester);
      $event = new ReleaseStateTransitionEvent('ready', $state);
      $subscriber->handleError($event);
    }

    $buildRequester = $this->getMockBuilder(BuildRequester::class)
      ->disableOriginalConstructor()
      ->getMock();

    $buildRequester->expects($this->once())
      ->method('requestFrontendBuild')
      ->with($this->callback(function ($reason) {
        $contains_str = str_contains($reason, 'Retrying build');
        $this->assertTrue($contains_str);
        return $contains_str;
      }));

    $subscriber = new ContentReleaseErrorSubscriber($buildRequester);
    $event = new ReleaseStateTransitionEvent('ready', 'error');
    $subscriber->handleError($event);
  }

  /**
   * Tests the ContentReleaseMetricsRecalculationSubscriber class.
   */
  public function testContentReleaseMetricsRecalculationSubscriber() {
    $collector = $this->getMockBuilder(MetricsCollectorManager::class)
      ->disableOriginalConstructor()
      ->getMock();

    $collector->expects($this->never())
      ->method('collectMetrics');

    $no_states = [
      'requested',
      'dispatched',
      'starting',
      'inprogress',
      'complete',
      'error',
    ];

    foreach ($no_states as $state) {
      $subscriber = new ContentReleaseMetricsRecalculationSubscriber($collector);
      $event = new ReleaseStateTransitionEvent('ready', $state);
      $subscriber->recalculateMetrics($event);
    }

    $collector = $this->getMockBuilder(MetricsCollectorManager::class)
      ->disableOriginalConstructor()
      ->getMock();

    $collector->expects($this->exactly(2))
      ->method('collectMetrics');

    $subscriber = new ContentReleaseMetricsRecalculationSubscriber($collector);
    $event = new ReleaseStateTransitionEvent('ready', 'ready');
    $subscriber->recalculateMetrics($event);
  }

  /**
   * Tests the ContentReleaseIntervalSubscriber class.
   *
   * @dataProvider contentReleaseIntervalSubscriberDataProvider
   */
  public function testContentReleaseIntervalSubscriber($statedata, $time, $event, $afterstate) {
    $state = new State(new KeyValueMemoryFactory());
    $state->setMultiple($statedata);

    $cris = new ContentReleaseIntervalSubscriber($state, $time);
    $cris->recordDeployInterval($event);

    foreach ($afterstate as $key => $value) {
      $this->assertEquals($value, $state->get($key));
    }
  }

  /**
   * Provides data for testContentReleaseIntervalSubscriber.
   */
  public function contentReleaseIntervalSubscriberDataProvider() {

    $now = time();
    $hour_ago = $now - 3600;

    $time = new SpecifiedTime(new RequestStack());
    $time->setCurrentTime($now);

    foreach (['ready', 'requested', 'dispatched', 'inprogress', 'error'] as $state) {
      yield 'no state data; no action on ' . $state => [
        [],
        $time,
        new ReleaseStateTransitionEvent('ready', $state),
        [
          ContentReleaseInterval::CONTENT_RELEASE_INTERVAL_STATE_KEY => NULL,
        ],
      ];
    }

    yield 'no state data; record complete event' => [
      [],
      $time,
      new ReleaseStateTransitionEvent('ready', 'complete'),
      [
        ContentReleaseInterval::CONTENT_RELEASE_INTERVAL_STATE_KEY => 0,
      ],
    ];

    yield 'has last release state data; record complete event' => [
      [
        ReleaseStateManager::LAST_RELEASE_COMPLETE_KEY => $hour_ago,
      ],
      $time,
      new ReleaseStateTransitionEvent('ready', 'complete'),
      [
        ContentReleaseInterval::CONTENT_RELEASE_INTERVAL_STATE_KEY => 3600,
      ],
    ];
  }

}
