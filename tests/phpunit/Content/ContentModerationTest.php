<?php

namespace tests\phpunit\Content;

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Form\FormStateInterface;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Test our requirements of the content moderation system.
 *
 * @group content_moderation
 */
class ContentModerationTest extends ExistingSiteBase {

  /**
   * Prevent accidental publication of un-proofed nodes.
   *
   * When a "Published" node is being edited, the default moderation state
   * should be set to "Draft".
   *
   * @dataProvider preventPublishingUnproofedNodesDataProvider
   */
  public function testPreventPublishingUnproofedNodes(
    array $element,
    string $baseFormId,
    array $expectedElement,
    bool $formEntityIsPublishable = FALSE,
    bool $formEntityIsPublished = FALSE
  ) {
    $context = [];
    $formStateProphecy = $this->prophesize(FormStateInterface::CLASS);
    if ($baseFormId === 'node_form') {
      $entityFormProphecy = $this->prophesize(EntityFormInterface::CLASS);
      if ($formEntityIsPublishable) {
        $entityProphecy = $this->prophesize(EntityPublishedInterface::CLASS);
        $entityProphecy->isPublished()->willReturn($formEntityIsPublished);
        $entityFormProphecy->getEntity()->willReturn($entityProphecy->reveal());
      }
      else {
        $entityProphecy = $this->prophesize(EntityInterface::CLASS);
        $entityFormProphecy->getEntity()->willReturn($entityProphecy->reveal());
      }
      $formStateProphecy->getFormObject()->willReturn($entityFormProphecy->reveal());
    }
    $formStateProphecy->getBuildInfo()->willReturn([
      'base_form_id' => $baseFormId,
    ]);
    $formState = $formStateProphecy->reveal();
    \va_gov_backend_field_widget_moderation_state_default_form_alter($element, $formState, $context);
    $this->assertEquals($expectedElement, $element);
  }

  /**
   * {@inheritDoc}
   */
  public function preventPublishingUnproofedNodesDataProvider() {
    return [
      // Case 1: By default, we shouldn't modify the element.
      [
        [],
        'test',
        [],
      ],
      // Case 2: But this time, with a better data structure.
      [
        [
          'state' => [
            '#default_value' => 'null',
            '#title' => 'Grand Poobah',
          ],
        ],
        'still_test_lol',
        [
          'state' => [
            '#default_value' => 'null',
            '#title' => 'Grand Poobah',
          ],
        ],
      ],
      // Case 3: Let's say it's node_form, but then not be publishable.
      [
        [
          'state' => [
            '#default_value' => 'null',
            '#title' => 'Grand Poobah',
          ],
        ],
        'node_form',
        [
          'state' => [
            '#default_value' => 'null',
            '#title' => 'Save as',
          ],
        ],
      ],
      // Case 4: Let's say it's node_form, and publishable, but not published.
      [
        [
          'state' => [
            '#default_value' => 'null',
            '#title' => 'Grand Poobah',
          ],
        ],
        'node_form',
        [
          'state' => [
            '#default_value' => 'null',
            '#title' => 'Save as',
          ],
        ],
        TRUE,
      ],
      // Case 5: Let's say it's node_form, and publishable, and published.
      [
        [
          'state' => [
            '#default_value' => 'null',
            '#title' => 'Grand Poobah',
          ],
        ],
        'node_form',
        [
          'state' => [
            '#default_value' => 'draft',
            '#title' => 'Save as',
          ],
        ],
        TRUE,
        TRUE,
      ],
    ];
  }

}
