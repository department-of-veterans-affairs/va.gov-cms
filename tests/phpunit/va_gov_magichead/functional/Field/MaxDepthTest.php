<?php

namespace tests\phpunit\va_gov_magichead\functional\Field;

use Tests\Support\Classes\VaGovExistingSiteBase;

/**
 * Tests the max depth for magichead.
 *
 * @group functional
 * @group all
 */
class MaxDepthTest extends VaGovExistingSiteBase {

  /**
   * Tests that the max depth has a correct default value.
   */
  public function testMaxDepthDefaultValue() {
    $user = $this->createUser([], 'Admin', TRUE);
    $this->drupalLogin($user);
    $this->drupalGet('admin/structure/taxonomy/manage/va_benefits_taxonomy/overview/form-display');
    $this->click('#edit-fields-field-va-benefit-eligibility-ov-settings-edit');
    $this->assertSession()->pageTextContains('The maximum depth of a magichead item.');
    $elements = $this->cssSelect('#edit-fields-field-va-benefit-eligibility-ov-settings-edit-form-settings-max-depth');
    $this->assertCount(1, $elements);
    $this->assertSame($elements[0]->getValue(), '3');
  }

}
