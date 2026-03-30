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
    $field_config = \Drupal::entityTypeManager()
      ->getStorage('field_config')
      ->load('taxonomy_term.va_benefits_taxonomy.field_va_benefit_eligibility_ov');
    $this->assertNotNull($field_config, 'Field config exists.');
    $this->assertSame(2, $field_config->getSetting('max_depth'));
  }

}
