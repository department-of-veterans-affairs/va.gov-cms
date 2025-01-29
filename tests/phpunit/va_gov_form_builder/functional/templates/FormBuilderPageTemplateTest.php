<?php

namespace tests\phpunit\va_gov_form_builder\functional\templates;

use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Functional test of page--va-gov-form-builder.html Twig template.
 *
 * This test suite takes the approach of testing a single route
 * that should result in rendering a page that utilizes the
 * page template under test. In this way, we can test the expected
 * behavior of the template file in a consistent manner, assuming
 * the route properly utilizes the theme (which is tested elsewhere).
 *
 * The route that makes the most sense here
 * is the `entry` route, as that should always be present, regardless
 * of potential future changes to Form Builder pages. The entry route
 * will redirect to the first content page.
 *
 * @group functional
 * @group all
 */
class FormBuilderPageTemplateTest extends VaGovExistingSiteBase {

  /**
   * {@inheritdoc}
   */
  private static $modules = ['va_gov_form_builder'];

  /**
   * Setup the environment for each test.
   */
  public function setUp(): void {
    parent::setUp();

    $this->drupalLogin($this->createUser(['access form builder']));

    // Form Builder entry.
    $this->drupalGet('/form-builder');
  }

  /**
   * Test that expected elements are present.
   */
  public function testExpectedElementsExist() {
    $this->assertSession()->statusCodeEquals(200);

    $containerElement = $this->cssSelect('.form-builder-page-container');
    $this->assertCount(1, $containerElement);

    $navbarElement = $this->cssSelect('.form-builder-subtitle');
    $this->assertCount(1, $navbarElement);
  }

  /**
   * Test that unexpected elements are not present.
   */
  public function testUnexpectedElementsDoNotExist() {
    $this->assertSession()->statusCodeEquals(200);

    $breadcrumbsElement = $this->cssSelect('#block-vagovclaro-breadcrumbs');
    $this->assertCount(0, $breadcrumbsElement);
  }

}
