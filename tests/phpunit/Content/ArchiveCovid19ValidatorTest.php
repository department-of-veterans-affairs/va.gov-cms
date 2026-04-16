<?php

namespace Tests\Content;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\va_gov_vamc\Plugin\Validation\Constraint\ArchiveCovid19;
use Drupal\va_gov_vamc\Plugin\Validation\Constraint\ArchiveCovid19Validator;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tests\Support\Classes\VaGovUnitTestBase;
use Tests\Support\Traits\ValidatorTestTrait;

/**
 * Tests for ArchiveCovid19Validator.
 *
 * @group unit
 * @group all
 * @group validation
 *
 * @coversDefaultClass ArchiveCovid19Validator
 */
class ArchiveCovid19ValidatorTest extends VaGovUnitTestBase {

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
   * @param array $referencedEntity
   *   An array containing:
   *   - [0]: The type of the referenced entity ('taxonomy_term' or 'node').
   *   - [1]: The ID of the referenced entity.
   *   - [2]: The title/label of the referenced entity.
   *
   * @covers ::validate
   * @dataProvider validateDataProvider
   */
  public function testValidate(bool $willValidate, string $moderationState, string $bundle, array $referencedEntity): void {
    $entity = $this->getEntityProphecy($moderationState, $bundle, $referencedEntity);

    $value = $this->prophesize(FieldItemListInterface::class);
    $value->getEntity()->willReturn($entity->reveal());

    $entityTypeManager = $this->getEntityTypeManagerProphecy($referencedEntity);

    // Override the static \Drupal::entityTypeManager() call.
    \Drupal::setContainer(
      new ContainerBuilder()
    );
    \Drupal::getContainer()->set('entity_type.manager', $entityTypeManager->reveal());

    // Now call your validator with the mocked objects.
    $validator = new ArchiveCovid19Validator();
    $this->prepareValidator($validator, $willValidate);
    $validator->validate($value->reveal(), new ArchiveCovid19());
  }

  /**
   * Sets up a mock entity.
   */
  public function getEntityProphecy(string $moderationState, string $bundle, array $referencedEntity): ObjectProphecy|NodeInterface {
    $entity = $this->prophesize(NodeInterface::class);
    $moderationStateField = new \stdClass();
    $moderationStateField->value = $moderationState;

    $refField = new \stdClass();
    $refField->target_id = $referencedEntity[1];

    $entity->bundle()->willReturn($bundle);
    $entity->moderation_state = $moderationStateField;

    if ($bundle === 'regional_health_care_service_des') {
      $entity->field_service_name_and_descripti = $refField;
    }
    elseif ($bundle === 'health_care_local_health_service') {
      $entity->field_regional_health_service = $refField;
    }
    return $entity;
  }

  /**
   * Sets up a mock entity type manager.
   */
  public function getEntityTypeManagerProphecy(array $referencedEntity) {
    $referencedEntityType = $referencedEntity[0];
    $referencedEntityId = $referencedEntity[1];
    $referencedEntityTitle = $referencedEntity[2];

    $entity = NULL;
    $entityStorage = NULL;
    $entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);

    if ($referencedEntityType === 'taxonomy_term') {
      $entity = $this->prophesize(TermInterface::class);
      $entityStorage = $this->prophesize(TermStorageInterface::class);
    }
    elseif ($referencedEntityType === 'node') {
      $entity = $this->prophesize(NodeInterface::class);
      $entityStorage = $this->prophesize(NodeStorageInterface::class);
    }
    $entity->label()->willReturn($referencedEntityTitle);
    $entityStorage->load($referencedEntityId)->willReturn($entity->reveal());
    $entityTypeManager->getStorage($referencedEntityType)->willReturn($entityStorage->reveal());

    return $entityTypeManager;
  }

  /**
   * Data provider.
   */
  public function validateDataProvider(): array {
    return [
      [
        FALSE,
        'published',
        'regional_health_care_service_des',
        ['taxonomy_term', 1, 'COVID-19 vaccines'],
      ],
      [
        FALSE,
        'published',
        'health_care_local_health_service',
        ['node', 2, 'COVID-19 vaccines'],
      ],
      [
        TRUE,
        'archived',
        'regional_health_care_service_des',
        ['taxonomy_term', 1, 'COVID-19 vaccines'],
      ],
      [
        TRUE,
        'archived',
        'health_care_local_health_service',
        ['node', 2, 'COVID-19 vaccines'],
      ],
      [
        TRUE,
        'published',
        'regional_health_care_service_des',
        ['taxonomy_term', 3, 'Some other service'],
      ],
      [
        TRUE,
        'published',
        'health_care_local_health_service',
        ['node', 4, 'Some other service'],
      ],
    ];
  }

}
