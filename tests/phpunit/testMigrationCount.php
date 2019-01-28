<?php

namespace tests\phpunit\migrationCount;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A test to confirm amount of nodes by type.
 */
class MigrationCount extends ExistingSiteBase {

  /**
   * A test method to deterine the amount of entities in the systems by type.
   *
   * @group migration
   *
   * @dataProvider validCounts
   */
  public function testCount($type, $expcount) {
    $nids = \Drupal::entityQuery('node')
      ->condition('type', $type)
      ->execute();
    $nodes = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadMultiple($nids);
    $count = count($nodes);
    $this->assertCount($expcount, $nodes, "Unexpected amount of nodes: Found " . $count . " for type '" . $type . "' expected " . $expcount);
  }

  /**
   * Returns entity types to test and expected number of the type.
   *
   * @return array
   *   Array containing entity type as string and expected count as int
   */
  public function validCounts() {
    return array(
      array("page", 40),
      array("landing_page", 1),
    );
  }

}
