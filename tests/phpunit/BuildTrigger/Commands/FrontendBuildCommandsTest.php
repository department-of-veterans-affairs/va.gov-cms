<?php

namespace tests\phpunit\BuildTrigger\Commands;

use Drush\TestTraits\DrushTestTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Functional test of the FrontendBuildCommands commands.
 */
class FrontendBuildCommandsTest extends ExistingSiteBase {
  use DrushTestTrait;

  /**
   * Tests drush commands.
   */
  public function testCommands() {
    $this->assertTrue(FALSE, 'Need to add tests for the frontend build commands.');
  }

}
