<?php

namespace tests\phpunit\BuildTrigger\Commands;

use Drush\TestTraits\DrushTestTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Functional test of the FrontendBuildStatusCommands commands.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\Commands\FrontendBuildStatusCommands
 */
class FrontendBuildStatusCommandsTest extends ExistingSiteBase {
  use DrushTestTrait;

  /**
   * Tests drush commands.
   */
  public function testCommands() {
    $this->drush('va-gov-get-frontend-build-status');
    $original = filter_var($this->getOutput(), FILTER_VALIDATE_BOOLEAN);

    $this->drush('va-gov-set-frontend-build-status', ['TRUE']);
    $this->assertContains('active', $this->getErrorOutput());

    $this->drush('va-gov-get-frontend-build-status');
    $this->assertContains('TRUE', $this->getOutput());

    $this->drush('va-gov-set-frontend-build-status', ['FALSE']);
    $this->assertContains('inactive', $this->getErrorOutput());

    $this->drush('va-gov-get-frontend-build-status');
    $this->assertContains('FALSE', $this->getOutput());

    $this->drush('va-gov-set-frontend-build-status', ['TRUE']);
    $this->assertContains('active', $this->getErrorOutput());

    $this->drush('va-gov-get-frontend-build-status');
    $this->assertContains('TRUE', $this->getOutput());

    $this->drush('va-gov-set-frontend-build-status', [$original ? 'TRUE' : 'FALSE']);
  }

}
