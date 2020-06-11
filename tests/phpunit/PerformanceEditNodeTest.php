<?php

namespace tests\phpunit;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm amount of nodes by type.
 */
class EditNodePerformance extends ExistingSiteBase {

  /**
   * A test method to deterine the amount of time to edit a node page.
   *
   * @group performance
   * @group all
   *
   * @dataProvider benchmarkTime
   */
  public function testEditNodePerformance($type, $benchmark) {
    // Creates a user. Will be automatically cleaned up at the end of the test.
    $author = $this->createUser();

    // Creates a node. Will be automatically cleaned up at the end of the test.
    $node = $this->createNode([
      'title' => 'Llama',
      'type' => $type,
      'uid' => $author->id(),
    ]);
    $node->setPublished()->save();

    // Start timer.
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;

    $node->setChangedTime(time())->save();

    // End timer.
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $microsecs = ($endtime - $starttime);

    // Test assertion.
    $secs = number_format($microsecs, 3);
    $this->assertLessThan($benchmark, $secs, __METHOD__ . "\nOperation took " . $secs . " seconds which is longer than the benchmark of " . $benchmark . " seconds for type " . $type . ".\n");

    $message = __METHOD__  . "\nOperation took " . $secs . " seconds compared to the benchmark of " . $benchmark . " seconds for type " . $type . ".\n";
    fwrite(STDERR, print_r($message, TRUE));
  }

  /**
   * Returns benchmark time to beat in order for test to succeed.
   *
   * @return array
   *   Array containing entity type as string and benchmark as int
   */
  public function benchmarkTime() {
    return [
      ["page", 5],
      ["landing_page", 5],
    ];
  }

}
