<?php

namespace tests\phpunit\FrontendBuild;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\KeyValueStore\KeyValueMemoryFactory;
use Drupal\Core\State\State;
use Drupal\va_gov_build_trigger\Plugin\MetricsCollector\ContentReleaseDuration;
use Drupal\va_gov_build_trigger\Plugin\MetricsCollector\ContentReleaseDurationRollingAverage;
use Drupal\va_gov_build_trigger\Plugin\MetricsCollector\ContentReleaseInterval;
use Drupal\va_gov_build_trigger\Plugin\MetricsCollector\ContentReleaseIntervalRollingAverage;
use Drupal\va_gov_build_trigger\Plugin\MetricsCollector\TimeSinceLastContentRelease;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
use Tests\Support\Classes\VaGovUnitTestBase;
use Tests\Support\Mock\SpecifiedTime;

/**
 * Unit test for build metrics.
 *
 * @group unit
 * @group all
 */
class BuildMetricsTest extends VaGovUnitTestBase {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    parent::setUp();

    \Drupal::unsetContainer();
    $container = new ContainerBuilder();

    $state = $this->getMockBuilder('Drupal\Core\State\StateInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $container->set('state', $state);
    $container->set('cache.bootstrap', $this->getMockBuilder('Drupal\Core\Cache\CacheBackendInterface')->getMock());
    $container->set('lock', $this->getMockBuilder('Drupal\Core\Lock\LockBackendInterface')->getMock());

    \Drupal::setContainer($container);

    $this->state = new State(new KeyValueMemoryFactory());
  }

  /**
   * Test the time since last content release metric.
   */
  public function testTimeSinceLastContentRelease() {
    $now = time();

    $this->state->set(ReleaseStateManager::LAST_RELEASE_COMPLETE_KEY, $now - 3900);
    $time = new SpecifiedTime(new RequestStack());
    $time->setCurrentTime($now);

    $plugin = new TimeSinceLastContentRelease(
      [],
      'test_plugin',
      ['title' => 'test', 'description' => 'test'],
      $this->state,
      $time
    );

    $values = $plugin->collectMetrics();

    $this->assertEquals(3900, $values[0]->getLabelledValues()[0]->getValue());
    $this->assertEquals(300, $values[1]->getLabelledValues()[0]->getValue());
  }

  /**
   * Test that metrics calculation works.
   *
   * @dataProvider durationMetricCalculationDataProvider
   * @dataProvider intervalMetricCalculationDataProvider
   * @dataProvider durationRollingAverageMetricCalculationDataProvider
   * @dataProvider intervalRollingAverageMetricCalculationDataProvider
   */
  public function testMetricCalculation($plugin_class, $statedata, $expected, $newstate) {
    $this->state->setMultiple($statedata);
    $plugin_instance = new $plugin_class(
      [],
      'test_plugin',
      ['title' => 'test', 'description' => 'test'],
      $this->state
    );
    $labeled_value = $plugin_instance->collectMetrics()[0]->getLabelledValues()[0];
    $this->assertEquals($expected, $labeled_value->getValue());

    foreach ($newstate as $key => $value) {
      $result = $this->state->get($key);
      $this->assertEquals($value, $result);
    }
  }

  /**
   * Provides data to exercise the ContentReleaseIntervalRollingAverage metric.
   */
  public function intervalRollingAverageMetricCalculationDataProvider() {
    $crira = ContentReleaseIntervalRollingAverage::class;

    yield 'no duration rolling average state data' => [
      $crira,
      [],
      0,
      [],
    ];

    $now = time();
    yield 'current and last interval; no rolling average' => [
      $crira,
      [
        ContentReleaseIntervalRollingAverage::CONTENT_RELEASE_INTERVAL_STATE_KEY => 300,
        ContentReleaseIntervalRollingAverage::LAST_UPDATED_CONTENT_RELEASE_INTERVAL_STATE_KEY => 400,
      ],
      380,
      [
        ContentReleaseIntervalRollingAverage::LAST_UPDATED_CONTENT_RELEASE_INTERVAL_STATE_KEY => 300,
        ContentReleaseIntervalRollingAverage::ROLLING_AVERAGE_STATE_KEY => 380,
      ],
    ];

    yield 'all intervals populated; decreasing rolling average' => [
      $crira,
      [
        ContentReleaseIntervalRollingAverage::CONTENT_RELEASE_INTERVAL_STATE_KEY => 300,
        ContentReleaseIntervalRollingAverage::LAST_UPDATED_CONTENT_RELEASE_INTERVAL_STATE_KEY => 400,
        ContentReleaseIntervalRollingAverage::ROLLING_AVERAGE_STATE_KEY => 380,
      ],
      364,
      [
        ContentReleaseIntervalRollingAverage::LAST_UPDATED_CONTENT_RELEASE_INTERVAL_STATE_KEY => 300,
        ContentReleaseIntervalRollingAverage::ROLLING_AVERAGE_STATE_KEY => 364,
      ],
    ];

    yield 'all intervals populated; increasing rolling average' => [
      $crira,
      [
        ContentReleaseIntervalRollingAverage::CONTENT_RELEASE_INTERVAL_STATE_KEY => 400,
        ContentReleaseIntervalRollingAverage::LAST_UPDATED_CONTENT_RELEASE_INTERVAL_STATE_KEY => 300,
        ContentReleaseIntervalRollingAverage::ROLLING_AVERAGE_STATE_KEY => 320,
      ],
      336,
      [
        ContentReleaseIntervalRollingAverage::LAST_UPDATED_CONTENT_RELEASE_INTERVAL_STATE_KEY => 400,
        ContentReleaseIntervalRollingAverage::ROLLING_AVERAGE_STATE_KEY => 336,
      ],
    ];
  }

  /**
   * Provides data to exercise the ContentReleaseDurationRollingAverage metric.
   */
  public function durationRollingAverageMetricCalculationDataProvider() {
    $crdra = ContentReleaseDurationRollingAverage::class;

    yield 'no duration rolling average state data' => [
      $crdra,
      [],
      0,
      [],
    ];

    $now = time();

    yield 'current and last duration; no rolling average' => [
      $crdra,
      [
        ContentReleaseDurationRollingAverage::CONTENT_RELEASE_DURATION_STATE_KEY => 300,
        ContentReleaseDurationRollingAverage::LAST_UPDATED_CONTENT_RELEASE_DURATION_STATE_KEY => 400,
      ],
      380,
      [
        ContentReleaseDurationRollingAverage::LAST_UPDATED_CONTENT_RELEASE_DURATION_STATE_KEY => 300,
        ContentReleaseDurationRollingAverage::ROLLING_AVERAGE_STATE_KEY => 380,
      ],
    ];

    yield 'all durations populated; decreasing rolling average' => [
      $crdra,
      [
        ContentReleaseDurationRollingAverage::CONTENT_RELEASE_DURATION_STATE_KEY => 300,
        ContentReleaseDurationRollingAverage::LAST_UPDATED_CONTENT_RELEASE_DURATION_STATE_KEY => 400,
        ContentReleaseDurationRollingAverage::ROLLING_AVERAGE_STATE_KEY => 380,
      ],
      364,
      [
        ContentReleaseDurationRollingAverage::LAST_UPDATED_CONTENT_RELEASE_DURATION_STATE_KEY => 300,
        ContentReleaseDurationRollingAverage::ROLLING_AVERAGE_STATE_KEY => 364,
      ],
    ];

    yield 'all durations populated; increasing rolling average' => [
      $crdra,
      [
        ContentReleaseDurationRollingAverage::CONTENT_RELEASE_DURATION_STATE_KEY => 400,
        ContentReleaseDurationRollingAverage::LAST_UPDATED_CONTENT_RELEASE_DURATION_STATE_KEY => 300,
        ContentReleaseDurationRollingAverage::ROLLING_AVERAGE_STATE_KEY => 320,
      ],
      336,
      [
        ContentReleaseDurationRollingAverage::LAST_UPDATED_CONTENT_RELEASE_DURATION_STATE_KEY => 400,
        ContentReleaseDurationRollingAverage::ROLLING_AVERAGE_STATE_KEY => 336,
      ],
    ];
  }

  /**
   * Provides data to exercise the ContentReleaseInterval metric.
   */
  public function intervalMetricCalculationDataProvider() {
    $cri = ContentReleaseInterval::class;

    yield 'no interval state data' => [
      $cri,
      [],
      0,
      [],
    ];

    yield 'has interval state data' => [
      $cri,
      [
        ContentReleaseInterval::CONTENT_RELEASE_INTERVAL_STATE_KEY => 1234,
      ],
      1234,
      [],
    ];
  }

  /**
   * Provides data to exercise the ContentReleaseDuration metric.
   */
  public function durationMetricCalculationDataProvider() {
    $crd = ContentReleaseDuration::class;

    yield 'no duration state data' => [
      $crd,
      [],
      0,
      [],
    ];

    $now = time();
    yield 'duration happy path' => [
      $crd,
      [
        ReleaseStateManager::LAST_RELEASE_DISPATCH_KEY => $now - 300,
        ReleaseStateManager::LAST_RELEASE_COMPLETE_KEY => $now,
      ],
      300,
      [
        ContentReleaseDuration::CONTENT_RELEASE_DURATION_STATE_KEY => 300,
      ],
    ];

    yield 'release in progress; no previous duration data' => [
      $crd,
      [
        ReleaseStateManager::LAST_RELEASE_DISPATCH_KEY => $now - 300,
        ReleaseStateManager::LAST_RELEASE_COMPLETE_KEY => $now - 400,
      ],
      0,
      [],
    ];

    yield 'release in progress; previous duration data exists' => [
      $crd,
      [
        ReleaseStateManager::LAST_RELEASE_DISPATCH_KEY => $now - 300,
        ReleaseStateManager::LAST_RELEASE_COMPLETE_KEY => $now - 400,
        ContentReleaseDuration::CONTENT_RELEASE_DURATION_STATE_KEY => 1234,
      ],
      1234,
      [],
    ];

    yield 'release not in progress; last release errored; previous duration data exists' => [
      $crd,
      [
        ReleaseStateManager::LAST_RELEASE_DISPATCH_KEY => $now - 300,
        ReleaseStateManager::LAST_RELEASE_ERROR_KEY => $now - 200,
        ContentReleaseDuration::CONTENT_RELEASE_DURATION_STATE_KEY => 1234,
      ],
      1234,
      [],
    ];
  }

}
