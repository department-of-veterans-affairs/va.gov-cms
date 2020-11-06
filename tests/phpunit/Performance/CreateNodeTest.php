<?php

namespace tests\phpunit\Performance;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm node creation performance.
 */
class CreateNodeTest extends ExistingSiteBase {

  /**
   * A test method to determine the amount of time it takes to create a node.
   *
   * @group performance
   * @group all
   *
   * @dataProvider benchmarkTime
   */
  public function testCreateNodePerformance($benchmark) {
    // Creates a user. Will be automatically cleaned up at the end of the test.
    $author = $this->createUser();

    // Start timer.
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;

    $node = $this->createNode([
      'title' => 'Llama',
      'type' => 'page',
      'uid' => $author->id(),
    ]);
    $node->setPublished()->save();

    // End timer.
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $microsecs = ($endtime - $starttime);

    // Test assertion.
    $secs = number_format($microsecs, 3);
    $this->assertLessThan($benchmark, $secs, __METHOD__ . "\nOperation took " . $secs . " seconds which is longer than the benchmark of " . $benchmark . " seconds.\n");

    $message = __METHOD__ . "\nOperation took " . $secs . " seconds compared to the benchmark of " . $benchmark . " seconds.\n";
    fwrite(STDERR, print_r($message, TRUE));
  }

  /**
   * Returns benchmark time to beat in order for test to succeed.
   *
   * @return array
   *   Array containing entity type as string and expected count as int
   */
  public function benchmarkTime() {
    return [[5]];
  }

}
