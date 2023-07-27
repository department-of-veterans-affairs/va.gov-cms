<?php

namespace tests\phpunit\va_gov_content_types\functional\Entity;

use Drupal\va_gov_content_types\Entity\VaNode;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the VaNode class.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultTrait \Drupal\va_gov_content_types\Entity\VaNode
 */
class VaNodeTest extends VaGovExistingSiteBase {

  /**
   * Verify that `page` nodes have this bundle class.
   */
  public function testBundleClass() {
    $node = $this->createNode([
      'type' => 'page',
    ]);
    $this->assertEquals(VaNode::class, get_class($node));
  }

}
