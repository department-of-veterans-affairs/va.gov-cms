<?php

namespace tests\phpunit\va_gov_live_field_migration\functional\FieldProvider\Resolver;

use Drupal\va_gov_live_field_migration\FieldProvider\Resolver\Resolver;
use Drupal\va_gov_live_field_migration\FieldProvider\Resolver\ResolverInterface;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of this service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_live_field_migration\FieldProvider\Resolver\Resolver
 */
class ResolverTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $resolver = \Drupal::service('va_gov_live_field_migration.field_provider_resolver');
    $this->assertInstanceOf(Resolver::class, $resolver);
    $this->assertInstanceOf(ResolverInterface::class, $resolver);
  }

}
