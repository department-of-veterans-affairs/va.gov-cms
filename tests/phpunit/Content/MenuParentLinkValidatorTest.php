<?php

namespace Tests\Content;

use Drupal\node\NodeInterface;
use Drupal\va_gov_vamc\Plugin\Validation\Constraint\MenuParentLink;
use Drupal\va_gov_vamc\Plugin\Validation\Constraint\MenuParentLinkValidator;
use Tests\Support\Classes\VaGovUnitTestBase;
use Tests\Support\Traits\ValidatorTestTrait;

/**
 * Tests for MenuParentLinkValidator.
 *
 * @group unit
 * @group all
 * @group validation
 *
 * @coversDefaultClass MenuParentLinkValidator
 */
class MenuParentLinkValidatorTest extends VaGovUnitTestBase {

  use ValidatorTestTrait;

  /**
   * Test ::validate().
   *
   * @param bool $willValidate
   *   TRUE if the test string should validate, FALSE otherwise.
   * @param string $moderationState
   *   The moderation state of the entity being validated.
   * @param string $bundle
   *   The bundle of the entity being validated.
   * @param string $parentLink
   *   The menu parent link value.
   *
   * @covers ::validate
   * @dataProvider validateDataProvider
   */
  public function testValidate(bool $willValidate, string $moderationState, string $bundle, string $parentLink): void {
    // Create a Node entity to be validated.
    $node = $this->prophesize(NodeInterface::class);
    $node->bundle()->willReturn($bundle);

    $menu['menu_parent'] = $parentLink;
    $moderationStateField = new \stdClass();
    $moderationStateField->value = $moderationState;

    $node->menu = $menu;
    $node->moderation_state = $moderationStateField;

    $validator = new MenuParentLinkValidator();
    $this->prepareValidator($validator, $willValidate);
    $validator->validate($node->reveal(), new MenuParentLink());
  }

  /**
   * Data provider for testValidate().
   *
   * @return array
   *   An array of test data sets.
   */
  public function validateDataProvider(): array {
    return [
      [
        FALSE,
        'published',
        'health_care_local_facility',
        'pittsburgh-health-care:',
      ],
      [
        TRUE,
        'published',
        'health_care_local_facility',
        'pittsburgh-health-care:menu_link_content:6e23deb4-87f9-4201-ab90-145410edaae2',
      ],
      [
        TRUE,
        'draft',
        'health_care_local_facility',
        'pittsburgh-health-care:',
      ],
      [
        TRUE,
        'published',
        'health_care_local_health_service',
        'pittsburgh-health-care:',
      ],
    ];
  }

}
