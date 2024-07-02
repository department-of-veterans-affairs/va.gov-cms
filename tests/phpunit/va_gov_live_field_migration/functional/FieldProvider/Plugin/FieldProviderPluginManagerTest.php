<?php

namespace tests\phpunit\va_gov_live_field_migration\functional\FieldProvider\Plugin;

use Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginManager;
use Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginManagerInterface;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of this service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_live_field_migration\FieldProvider\Plugin\FieldProviderPluginManager
 */
class FieldProviderPluginManagerTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $pluginManager = \Drupal::service('plugin.manager.va_gov_live_field_migration.field_provider');
    $this->assertInstanceOf(FieldProviderPluginManager::class, $pluginManager);
    $this->assertInstanceOf(FieldProviderPluginManagerInterface::class, $pluginManager);
  }

}
