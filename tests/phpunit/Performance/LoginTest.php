<?php

namespace tests\phpunit\Performance;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm login performance.
 */
class LoginTest extends ExistingSiteBase {

  /**
   * A test method to determine the amount of time to load the Login page.
   *
   * @group performance
   * @group all
   *
   * @dataProvider benchmarkTime
   */
  public function testLoginPerformance($benchmark) {

    // Creates a user. Will be automatically cleaned up at the end of the test.
    $author = $this->createUser();
    $author->addRole('content_editor');
    $author->save();

    // Warm cache before testing so login test will be realistic.
    $this->drupalLogin($author);

    // Logout here because if we don't then drupalLogin() automatically calls
    // drupalLogout() on the next drupalLogin() invocation,
    // which is unrealistic.
    $this->drupalLogout();

    // Start timer.
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;

    $this->drupalLogin($author);

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
   * @see https://github.com/department-of-veterans-affairs/va.gov-cms/issues/1657#issuecomment-631755887
   *
   * @return array
   *   Array containing entity type as string and expected count as int
   */
  public function benchmarkTime() {
    return [
      [3],
    ];
  }

}
