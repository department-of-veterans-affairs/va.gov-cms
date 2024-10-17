<?php

namespace tests\phpunit\va_gov_live_field_migration\functional\Migration\Plugin;

use Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginManager;
use Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginManagerInterface;
use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of this service.
 *
 * @group functional
 * @group all
 *
 * @coversDefaultClass \Drupal\va_gov_live_field_migration\Migration\Plugin\MigrationPluginManager
 */
class MigrationPluginManagerTest extends VaGovExistingSiteBase {

  /**
   * Test that the service is available.
   *
   * @covers ::__construct
   */
  public function testConstruct() {
    $pluginManager = \Drupal::service('plugin.manager.va_gov_live_field_migration.migration');
    $this->assertInstanceOf(MigrationPluginManager::class, $pluginManager);
    $this->assertInstanceOf(MigrationPluginManagerInterface::class, $pluginManager);
  }

}
