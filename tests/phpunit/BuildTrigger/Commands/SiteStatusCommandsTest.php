<?php

namespace tests\phpunit\BuildTrigger\Commands;

use Drush\TestTraits\DrushTestTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Functional test of the SiteStatusCommands commands.
 *
 * @coversDefaultClass \Drupal\va_gov_build_trigger\Commands\SiteStatusCommands
 */
class SiteStatusCommandsTest extends ExistingSiteBase {
  use DrushTestTrait;

  /**
   * Tests drush commands.
   */
  public function testCommands() {
    $this->drush('va-gov-get-deploy-mode');
    $original = filter_var($this->getOutput(), FILTER_VALIDATE_BOOLEAN);

    $this->drush('va-gov-set-deploy-mode', ['TRUE']);
    $this->assertContains('enabled', $this->getErrorOutput());

    $this->drush('va-gov-get-deploy-mode');
    $this->assertContains('TRUE', $this->getOutput());

    $this->drush('va-gov-set-deploy-mode', ['FALSE']);
    $this->assertContains('disabled', $this->getErrorOutput());

    $this->drush('va-gov-get-deploy-mode');
    $this->assertContains('FALSE', $this->getOutput());

    $this->drush('va-gov-set-deploy-mode', ['TRUE']);
    $this->assertContains('enabled', $this->getErrorOutput());

    $this->drush('va-gov-get-deploy-mode');
    $this->assertContains('TRUE', $this->getOutput());

    $this->drush('va-gov-disable-deploy-mode');
    $this->assertContains('disabled', $this->getErrorOutput());

    $this->drush('va-gov-get-deploy-mode');
    $this->assertContains('FALSE', $this->getOutput());

    $this->drush('va-gov-enable-deploy-mode');
    $this->assertContains('enabled', $this->getErrorOutput());

    $this->drush('va-gov-get-deploy-mode');
    $this->assertContains('TRUE', $this->getOutput());

    $this->drush('va-gov-set-deploy-mode', [$original ? 'TRUE' : 'FALSE']);
  }

}
