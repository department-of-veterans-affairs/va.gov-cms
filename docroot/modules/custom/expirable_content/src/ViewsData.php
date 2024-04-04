<?php

namespace Drupal\expirable_content;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides the expirable_content views integration.
 *
 * @internal
 */
class ViewsData {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The expirable content information service.
   *
   * @var \Drupal\expirable_content\ExpirableContentInformation
   */
  protected ExpirableContentInformation $expirableContentInfo;

  /**
   * Creates a new ViewsData instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\expirable_content\ExpirableContentInformation $expirableContentInformation
   *   The expirable_content.information service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ExpirableContentInformation $expirableContentInformation) {
    $this->entityTypeManager = $entity_type_manager;
    $this->expirableContentInfo = $expirableContentInformation;
  }

  /**
   * Returns the Views data.
   *
   * Adds the expiration and warning date computed fields to Views to all
   * base and revision tables for any type that is expirable.
   *
   * @return array
   *   The Views data.
   */
  public function getViewsData(): array {
    $data = [];

    $entity_types = array_filter($this->entityTypeManager->getDefinitions(), function (EntityTypeInterface $type) {
      return $this->expirableContentInfo->isExpirableEntityType($type);
    });

    foreach ($entity_types as $entity_type) {
      $table = $entity_type->getDataTable() ?: $entity_type->getBaseTable();

      // Add 'Expiration date' computed field to base tables.
      $data[$table]['expiration_date'] = [
        'title' => $this->t('Expiration date'),
        'field' => [
          'id' => 'expirable_content_field',
          'default_formatter' => 'date',
          'field_name' => 'expiration_date',
        ],
        'filter' => ['id' => 'expirable_content_filter', 'allow empty' => TRUE],
        'sort' => ['id' => 'expirable_content_sort'],
      ];

      // Add 'Warning date' computed field to base tables.
      $data[$table]['warning_date'] = [
        'title' => $this->t('Warning date'),
        'field' => [
          'id' => 'expirable_content_field',
          'default_formatter' => 'date',
          'field_name' => 'warning_date',
        ],
        'filter' => ['id' => 'expirable_content_filter', 'allow empty' => TRUE],
        'sort' => ['id' => 'expirable_content_sort'],
      ];

      $revision_table = $entity_type->getRevisionDataTable() ?: $entity_type->getRevisionTable();

      // Add 'Expiration date' computed field to revision base tables.
      $data[$revision_table]['expiration_date'] = [
        'title' => $this->t('Expiration date'),
        'field' => [
          'id' => 'expirable_content_field',
          'default_formatter' => 'date',
          'field_name' => 'expiration_date',
        ],
        'filter' => ['id' => 'expirable_content_filter', 'allow empty' => TRUE],
        'sort' => ['id' => 'expirable_content_sort'],
      ];
      // Add 'Warning date' computed field to revision base tables.
      $data[$revision_table]['moderation_state'] = [
        'title' => $this->t('Warning date'),
        'field' => [
          'id' => 'expirable_content_field',
          'default_formatter' => 'date',
          'field_name' => 'warning_date',
        ],
        'filter' => ['id' => 'expirable_content_filter', 'allow empty' => TRUE],
        'sort' => ['id' => 'expirable_content_sort'],
      ];
    }

    return $data;
  }

}
