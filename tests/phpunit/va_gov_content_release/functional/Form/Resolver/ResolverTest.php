<?php

namespace tests\phpunit\va_gov_content_release\functional\Form\Resolver;

use Drupal\va_gov_content_release\Form\Resolver\Resolver;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Form Resolver service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\Form\Resolver\Resolver
 */
class ResolverTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $resolver = \Drupal::service('va_gov_content_release.form_resolver');
    $this->assertInstanceOf(Resolver::class, $resolver);
  }

}
