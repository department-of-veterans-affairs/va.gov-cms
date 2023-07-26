<?php

namespace tests\phpunit\va_gov_content_release\functional\EntityEvent\Strategy\Resolver;

use Drupal\va_gov_content_release\EntityEvent\Strategy\Resolver\Resolver;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Entity Event Strategy Resolver service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\EntityEventStrategy\Resolver\Resolver
 */
class ResolverTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $resolver = \Drupal::service('va_gov_content_release.entity_event_strategy_resolver');
    $this->assertInstanceOf(Resolver::class, $resolver);
  }

}
