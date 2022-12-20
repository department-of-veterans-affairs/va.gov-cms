<?php

namespace tests\phpunit\FrontendBuild;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\KeyValueStore\KeyValueMemoryFactory;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\State\State;
use Drupal\va_gov_build_trigger\Service\BuildRequester;
use Drupal\va_gov_build_trigger\Service\BuildScheduler;
use Symfony\Component\DependencyInjection\Container;
use Tests\Support\Mock\SpecifiedTime;
use Tests\Support\Classes\VaGovUnitTestBase;

/**
 * Unit test for the build scheduler.
 *
 * @group unit
 * @group all
 */
class BuildSchedulerTest extends VaGovUnitTestBase {

  /**
   * The build requester service.
   *
   * @var \Drupal\va_gov_build_trigger\Service\BuildRequesterInterface
   */
  protected $buildRequester;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Used for \Drupal\Component\Datetime\TimeInterface typehints.
   *
   * @var \Tests\Support\Mock\SpecifiedTime
   */
  protected $time;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    parent::setUp();

    $this->state = new State(new KeyValueMemoryFactory());
    $this->time = new SpecifiedTime(new RequestStack());

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
   * Test cases for scheduled build logic.
   *
   * For reference, scheduled hours are Monday through Friday, 12:00-24:00
   * (00:00 +1) GMT.
   */
  public function scheduledBuildDataProvider() {
    return [
      // Normal cases.
      'weekend, before business hours, build never requested' => [
        // Sun May 01 2022 11:00:00 GMT+0000.
        1651402800,
        0,
        FALSE,
      ],
      'weekend, before business hours, build requested 65 minutes ago' => [
        // Sun May 01 2022 11:00:00 GMT+0000.
        1651402800,
        // Sun May 01 2022 09:55:00 GMT+0000.
        1651398900,
        FALSE,
      ],
      'weekend, during business hours, build never requested' => [
        // Sun May 01 2022 14:00:00 GMT+0000.
        1651413600,
        0,
        FALSE,
      ],
      'weekend, during business hours, build requested 65 minutes ago' => [
        // Sun May 01 2022 14:00:00 GMT+0000.
        1651413600,
        // Sun May 01 2022 12:55:00 GMT+0000.
        1651409700,
        FALSE,
      ],
      'weekend, after business hours, build never requested' => [
        // Sun May 01 2022 22:00:00 GMT+0000.
        1651442400,
        0,
        FALSE,
      ],
      'weekend, after business hours, build requested 65 minutes ago' => [
        // Sun May 01 2022 22:00:00 GMT+0000.
        1651442400,
        // Sun May 01 2022 20:55:00 GMT+0000.
        1651438500,
        FALSE,
      ],
      'weekday, before business hours, build never requested' => [
        // Mon May 02 2022 11:00:00 GMT+0000.
        1651489200,
        0,
        FALSE,
      ],
      'weekday, before business hours, build requested 65 minutes ago' => [
        // Mon May 02 2022 11:00:00 GMT+0000.
        1651489200,
        // Mon May 02 2022 09:55:00 GMT+0000.
        1651485300,
        FALSE,
      ],
      'weekday, during business hours, build never requested' => [
        // Mon May 02 2022 15:00:00 GMT+0000.
        1651503600,
        0,
        TRUE,
      ],
      'weekday, during business hours, build requested 65 minutes ago' => [
        // Mon May 02 2022 15:00:00 GMT+0000.
        1651503600,
        // Mon May 02 2022 13:55:00 GMT+0000.
        1651499700,
        TRUE,
      ],
      'weekday, after business hours, build never requested' => [
        // Mon May 03 2022 01:00:00 GMT+0000.
        1651539600,
        0,
        FALSE,
      ],
      'weekday, after business hours, build requested 65 minutes ago' => [
        // Mon May 03 2022 01:00:00 GMT+0000.
        1651539600,
        // Mon May 02 2022 20:55:00 GMT+0000.
        1651535700,
        FALSE,
      ],

      // Edge cases:
      // Ensure that business hours end at 8pm ET -- protect against
      // inadvertently including 8:59 since we're only checking the hour.
      'weekday, 8:01pm, build never requested' => [
        // Mon May 03 2022 12:01:00 GMT+0000.
        1651536060,
        0,
        FALSE,
      ],
    ];
  }

  /**
   * Test that we're getting state values back from getState().
   *
   * @dataProvider scheduledBuildDataProvider
   */
  public function testCheckScheduledBuild($time, $last_scheduled_build_request, $should_request_build) {
    $buildRequester = $this->getBuildRequester($should_request_build);
    $this->time->setCurrentTime($time);
    $this->state->set(BuildScheduler::VA_GOV_LAST_SCHEDULED_BUILD_REQUEST, $last_scheduled_build_request);

    $buildScheduler = new BuildScheduler($buildRequester, $this->state, $this->time, $this->dateFormatter);
    $buildScheduler->checkScheduledBuild();

    // If the build was requested, make sure that the timestamp was updated
    // properly.
    if ($should_request_build) {
      $this->assertEquals($time, $this->state->get(BuildScheduler::VA_GOV_LAST_SCHEDULED_BUILD_REQUEST));
    }
  }

  /**
   * Get a mocked build requester that will require/disallow requesting a build.
   *
   * @param bool $shouldExpectBuildRequest
   *   Whether or not the build requester should get a request.
   *
   * @return \Drupal\va_gov_build_trigger\Service\BuildRequesterInterface
   *   (mocked)
   */
  protected function getBuildRequester($shouldExpectBuildRequest = FALSE) {
    $build_requester = $this->getMockBuilder(BuildRequester::class)
      ->disableOriginalConstructor()
      ->getMock();

    if ($shouldExpectBuildRequest) {
      $build_requester->expects($this->once())
        ->method('requestFrontendBuild')
        ->with('Scheduled hourly build');
    }
    else {
      $build_requester->expects($this->never())
        ->method('requestFrontendBuild');
    }

    return $build_requester;
  }

}
