<?php

namespace tests\phpunit\va_gov_content_release\functional\Strategy\Resolver;

use Drupal\va_gov_content_release\Strategy\Resolver\Resolver;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Strategy Resolver service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\Strategy\Resolver\Resolver
 */
class ResolverTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $resolver = \Drupal::service('va_gov_content_release.strategy_resolver');
    $this->assertInstanceOf(Resolver::class, $resolver);
  }

}
