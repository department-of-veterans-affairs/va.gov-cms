<?php

namespace tests\phpunit;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm ability to create taxonomy.
 */
class CreateTaxonomyTerm extends ExistingSiteBase {

  /**
   * A test method to determine the ability and time to create a node.
   *
   * @group performance
   * @group functional
   * @group all
   *
   * @dataProvider benchmarkTime
   */
  public function testCreateTaxonomyTerm($benchmark) {
    $vocab = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->load('health_care_service_taxonomy');

    // Start timer.
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;

    // Creates a term. Will be automatically cleaned up at the end of the test.
    $term = $this->createTerm($vocab);
    $this->assertNotEmpty($term->getName(), 'Failed to create term');

    // End timer.
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $microsecs = ($endtime - $starttime);

    // Test assertion.
    $secs = number_format($microsecs, 3);
    $this->assertLessThan($benchmark, $secs, __METHOD__  . "\nOperation took " . $secs . " seconds which is longer than the benchmark of " . $benchmark . " seconds.\n");

    $message = __METHOD__  . "\nOperation took " . $secs . " seconds compared to the benchmark of " . $benchmark . " seconds.\n";
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
