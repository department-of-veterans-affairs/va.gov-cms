<?php

namespace tests\phpunit;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to assure the performance of the workbench dashboard.
 */
class WorkbenchPerformance extends ExistingSiteBase {

  /**
   * A test method to deterine the amount of time it takes to load workbench.
   *
   * @group performance
   * @group all
   *
   * @dataProvider benchmarkTime
   */
  public function testWorkbenchPerformance($benchmark) {

    $account = $this->createUser();
    $account->addRole('administrator');
    $account->save();

    $this->drupalLogin($account);

    // Start timer.
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;

    $this->visit('/user/1/moderation/dashboard');

    $this->assertEquals($this->getSession()->getStatusCode(), '200', 'Workbench did not load properly');

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
      [5],
    ];
  }

}
