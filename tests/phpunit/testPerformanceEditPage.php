<?php

namespace tests\phpunit;

use weitzman\DrupalTestTraits\ExistingSiteBase;
use Drupal\node\Entity\Node;

/**
 * A test to confirm amount of nodes by type.
 */
class EditNodePerformance extends ExistingSiteBase {

  /**
   * A test method to deterine the amount of time to edit a node page.
   *
   * @dataProvider benchmarkTime
   */
  public function testEditNodePerformance($benchmark) {
    // Creates a user. Will be automatically cleaned up at the end of the test.
    $author = $this->createUser();

    $nids = \Drupal::entityQuery('node')->condition('type', 'page')->execute();
    shuffle($nids);
    $nid = array_pop($nids);

    // Start timer.
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;

    $node = Node::load($nid);
    $node->setChangedTime(time())->save();

    // End timer.
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $microsecs = ($endtime - $starttime);

    // Test assertion.
    $secs = number_format($microsecs, 3);
    $this->assertLessThan($benchmark, $secs, "\nOperation took " . $secs . " seconds which is longer than the benchmark of " . $benchmark . " seconds.\n");

    $message = "\nOperation took " . $secs . " seconds compared to the benchmark of " . $benchmark . " seconds.\n";
    fwrite(STDERR, print_r($message, TRUE));
  }

  /**
   * Returns benchmark time to beat in order for test to succeed.
   *
   * @return array
   *   Array containing entity type as string and expected count as int
   */
  public function benchmarkTime() {
    return [
      [2],
    ];
  }

}
