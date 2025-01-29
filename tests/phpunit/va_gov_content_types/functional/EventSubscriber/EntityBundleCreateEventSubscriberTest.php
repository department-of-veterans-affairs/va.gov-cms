<?php

namespace tests\phpunit\va_gov_content_types\functional\EventSubscriber;

use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\va_gov_content_types\EventSubscriber\EntityBundleCreateEventSubscriber;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of the EntityBundleCreateEventSubscriber class.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_content_types\EventSubscriber\EntityBundleCreateEventSubscriber
 */
class EntityBundleCreateEventSubscriberTest extends VaGovExistingSiteBase {

  use ContentTypeCreationTrait;

  /**
   * Verify that the service is registered and can be instantiated.
   */
  public function testServiceRegistered(): void {
    $this->assertInstanceOf(EntityBundleCreateEventSubscriber::class, $this->container->get('va_gov_content_types.entity_bundle_create_event_subscriber'));
  }

  /**
   * Verify the prometheus_exporter config is updated.
   */
  public function testOnEntityBundleCreate(): void {
    $config = $this->container->get('config.factory')->get('prometheus_exporter.settings');
    $bundles = $config->get('collectors.node_count.settings.bundles') ?? [];
    // Generate a random machine name that is unlikely to exist.
    $typeName = strtolower($this->randomMachineName(8));
    $this->assertArrayNotHasKey($typeName, $bundles);

    $this->createContentType([
      'type' => $typeName,
      'name' => 'My Test Content Type ' . $typeName,
    ]);

    $config = $this->container->get('config.factory')->get('prometheus_exporter.settings');
    $bundles = $config->get('collectors.node_count.settings.bundles') ?? [];
    $this->assertArrayHasKey($typeName, $bundles);
  }

}
