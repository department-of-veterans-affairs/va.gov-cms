<?php

namespace tests\phpunit\FrontendBuild;

use Drupal\Core\KeyValueStore\KeyValueMemoryFactory;
use Drupal\Core\State\State;
use Drupal\prometheus_exporter\MetricsCollectorManager;
use Drupal\va_gov_build_trigger\Event\ReleaseStateTransitionEvent;
use Drupal\va_gov_build_trigger\EventSubscriber\ContentReleaseErrorSubscriber;
use Drupal\va_gov_build_trigger\EventSubscriber\ContentReleaseIntervalSubscriber;
use Drupal\va_gov_build_trigger\EventSubscriber\ContentReleaseMetricsRecalculationSubscriber;
use Drupal\va_gov_build_trigger\EventSubscriber\ContinuousReleaseSubscriber;
use Drupal\va_gov_build_trigger\Plugin\MetricsCollector\ContentReleaseInterval;
use Drupal\va_gov_build_trigger\Service\BuildRequester;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Support\Mock\SpecifiedTime;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\Container;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test for build state event subscribers.
 *
 * @group unit
 * @group all
 */
class BuildStateEventSubscriberTest extends VaGovUnitTestBase {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->state = new State(new KeyValueMemoryFactory());

    $this->dateFormatter = new DateFormatter(
      $this->getMockBuilder(EntityTypeManagerInterface::class)->disableOriginalConstructor()->getMock(),
      $this->getMockBuilder(LanguageManagerInterface::class)->disableOriginalConstructor()->getMock(),
      $this->getStringTranslationStub(),
      $this->getConfigFactoryStub(),
      new RequestStack()
    );

    // Override the config factory service because DateFormatter gets it
    // directly from \Drupal 🙁 .
    $container = new Container();
    $container->set(
      'config.factory',
      $this->getConfigFactoryStub([
        'system.date' => [
          'country.default' => 'US',
        ],
      ]),
    );
    \Drupal::setContainer($container);
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
      $subscriber = new ContentReleaseErrorSubscriber($buildRequester, $this->state);
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

    $subscriber = new ContentReleaseErrorSubscriber($buildRequester, $this->state);
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

  /**
   * Tests the ContinuousReleaseSubscriber class.
   *
   * @dataProvider continuousReleaseTestDataProvider
   */
  public function testContinuousRelease($continuous_release_enabled, $event, $time, $expected) {
    $state = new State(new KeyValueMemoryFactory());
    $state->set(ContinuousReleaseSubscriber::CONTINUOUS_RELEASE_ENABLED, $continuous_release_enabled);

    $buildRequester = $this->getMockBuilder(BuildRequester::class)
      ->disableOriginalConstructor()
      ->getMock();

    if ($expected) {
      $buildRequester->expects($this->once())
        ->method('requestFrontendBuild');
    }
    else {
      $buildRequester->expects($this->never())
        ->method('requestFrontendBuild');
    }

    $continuous_release_subscriber = new ContinuousReleaseSubscriber($state, $time, $buildRequester, $this->dateFormatter);

    $continuous_release_subscriber->releaseContinuously($event);
  }

  /**
   * Provides data for testContentReleaseIntervalSubscriber.
   */
  public function continuousReleaseTestDataProvider() {
    $release_states = [
      'no' => [
        'ready',
        'requested',
        'dispatched',
        'inprogress',
        'error',
      ],
      'yes' => [
        'complete',
      ],
    ];

    $test_times = [
      'no' => [
        'weekend, before business hours' => SpecifiedTime::createFromTime(1651402800),
        'weekend, during business hours' => SpecifiedTime::createFromTime(1651413600),
        'weekend, after business hours' => SpecifiedTime::createFromTime(1651442400),
        'weekday, before business hours' => SpecifiedTime::createFromTime(1651489200),
        'weekday, after business hours' => SpecifiedTime::createFromTime(1651528800),
      ],
      'yes' => [
        'weekday, during business hours' => SpecifiedTime::createFromTime(1651503600),
      ],
    ];

    $continuous_release_enabled = FALSE;

    foreach ($release_states as $state_expected => $state_list) {
      foreach ($state_list as $new_release_state) {
        foreach ($test_times as $time_expected => $time_list) {
          foreach ($time_list as $test_time_description => $test_time) {
            // No matter what state or time we're in, if continuous releases are
            // disabled, we expect them to not trigger.
            yield 'continuous release disabled; ' . $test_time_description . '; release state ' . $new_release_state => [
              $continuous_release_enabled,
              new ReleaseStateTransitionEvent('ready', $new_release_state),
              $test_time,
              FALSE,
            ];
          }
        }
      }
    }

    $continuous_release_enabled = TRUE;

    foreach ($release_states as $state_expected => $state_list) {
      foreach ($state_list as $new_release_state) {
        foreach ($test_times as $time_expected => $time_list) {
          foreach ($time_list as $test_time_description => $test_time) {
            $release_state_should_trigger = ($state_expected == 'yes');
            $time_should_trigger = ($time_expected == 'yes');

            // If all of the conditions are met, then should trigger. Otherwise,
            // it should not trigger.
            yield 'continuous release enabled; ' . $test_time_description . '; release state ' . $new_release_state => [
              $continuous_release_enabled,
              new ReleaseStateTransitionEvent('ready', $new_release_state),
              $test_time,
              ($release_state_should_trigger && $time_should_trigger),
            ];
          }
        }
      }
    }
  }

}
