<?php

namespace tests\phpunit;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm amount of nodes by type.
 */
class LoginPerformance extends ExistingSiteBase {

  /**
   * A test method to determine the amount of time to load the Login page.
   *
   * @group performance
   * @group all
   *
   * @dataProvider benchmarkTime
   */
  public function testLoginPerformance($benchmark) {

    $author = $this->createUser();
    $author->addRole('content_editor');
    $author->save();

    // Warm some cache before testing so login test will be more realistic.
    $this->drupalLogin($author);

    // Start timer.
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;

    // Creates a user. Will be automatically cleaned up at the end of the test.
    $this->drupalLogin($author);

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
    return array(
      array(2),
    );
  }

}
