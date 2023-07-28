<?php

namespace tests\phpunit\va_gov_content_release\functional\Form\Resolver;

use Drupal\va_gov_content_release\EventSubscriber\EntityEventSubscriber;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the Entity Event Subscriber service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_release\EventSubscriber\EntityEventSubscriber
 */
class EntityEventSubscriberTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $entityEventSubscriber = \Drupal::service('va_gov_content_release.entity_event_subscriber');
    $this->assertInstanceOf(EntityEventSubscriber::class, $entityEventSubscriber);
  }

}
