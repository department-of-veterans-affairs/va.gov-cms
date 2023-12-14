<?php

namespace tests\phpunit\API;

use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Tests to confirm JSON:API Explorer UI works as expected.
 *
 * @group functional
 * @group all
 */
class JsonApiExplorerUiTest extends VaGovExistingSiteBase {

  /**
   * Test JSON:API Explorer UI loads.
   *
   * @group services
   * @group all
   */
  public function testJsonApiExplorerUiLoads() {
    $user = $this->createUser();
    $user->addRole('content_api_consumer');
    $user->save();

    $this->drupalLogin($user);
    $this->drupalGet('/admin/config/services/openapi/swagger/jsonapi');

    // Confirm that the page is accessible.
    $this->assertSession()->statusCodeEquals(200);

    // Assert that ".page-title" is "OpenAPI Documentation".
    $this->assertSession()->elementTextContains(
      'css',
      '.page-title',
      'OpenAPI Documentation'
    );

    // Any further assertions would need to be done with JavaScript test bases,
    // such as Nightwatch. Or this test could be moved to Cypress.
    // But this is a good start...
  }

}
