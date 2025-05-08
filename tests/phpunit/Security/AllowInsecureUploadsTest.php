<?php

namespace tests\phpunit\Security;

use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * A test to confirm that allow_insecure_uploads is always set to false.
 *
 * @group functional
 * @group security
 */
class AllowInsecureUploadsTest extends VaGovExistingSiteBase {

  /**
   * Test that allow_insecure_uploads is always set to false.
   */
  public function testAllowInsecureUploadsIsFalse() {
    // Load the system.file configuration.
    $config = $this->container->get('config.factory')->get('system.file');

    // Assert that allow_insecure_uploads is set to false.
    $this->assertFalse(
      $config->get('allow_insecure_uploads'),
      'The allow_insecure_uploads setting is not set to false.'
    );
  }

}
