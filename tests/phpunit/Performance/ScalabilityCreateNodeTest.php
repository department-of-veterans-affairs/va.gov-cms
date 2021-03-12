<?php

namespace tests\phpunit\Performance;

use Drupal\Component\Utility\Timer;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm amount of nodes by type.
 */
class ScalabilityCreateNodeTest extends ExistingSiteBase {

  public const TIMER_NAME = 'php-unit-scalable-page-test';

  /**
   * A test method to determine the amount of time it takes to create a node.
   *
   * @group performance
   * @group all
   */
  public function testScalabilityCreateNodeTest() {
    $count = $this->runPerformanceTest();

    $microsecs = Timer::read(static::TIMER_NAME);
    $secs = number_format($microsecs / 1000, 2);

    Timer::stop(static::TIMER_NAME);

    $message = __METHOD__ . "\nOperation took " . $secs . " and completed $count iterations.\n";
    fwrite(STDERR, print_r($message, TRUE));
  }

  /**
   * Run the performance test.
   *
   * @return int
   *   A count of the number of nodes.
   */
  protected function runPerformanceTest() : int {
    Timer::start(static::TIMER_NAME);
    $endtime = $this->getMilliseconds();

    $cur_time = 0;
    $count = 0;

    $author = $this->createUser();

    while ($cur_time < $endtime) {
      $cur_time = Timer::read(static::TIMER_NAME);
      $node = $this->createNode([
        'title' => $this->randomString(),
        'type' => 'page',
        'uid' => $author->id(),
      ]);
      $node->setPublished()->save();
      $count++;
    }

    return $count;
  }

  /**
   * Get the number of milliseconds to run.
   *
   * @return int
   *   The number of milliseconds to run.
   */
  protected function getMilliseconds() : int {
    return 3000;
  }

}
