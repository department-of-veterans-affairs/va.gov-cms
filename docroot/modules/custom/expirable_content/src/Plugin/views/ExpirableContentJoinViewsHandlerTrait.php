<?php

namespace Drupal\expirable_content\Plugin\views;

use Drupal\views\Views;

/**
 * Assist views handler plugins to join to the expirable_content entity.
 *
 * The concept of this trait is borrowed from the content_moderation trait:
 * \Drupal\content_moderation\Views\ModerationStateJoinViewsHandlerTrait()
 *
 * @internal
 */
trait ExpirableContentJoinViewsHandlerTrait {

  /**
   * {@inheritdoc}
   */
  public function ensureMyTable() {
    if (!isset($this->tableAlias)) {
      $table_alias = $this->query->ensureTable($this->table, $this->relationship);

      // Join the expirable content entity to by joining either the entity
      // field data or revision table. This allows filtering states against
      // either the default or latest revision, depending on the relationship of
      // the filter.
      $left_entity_type = $this->entityTypeManager->getDefinition($this->getEntityType());
      $entity_type = $this->entityTypeManager->getDefinition('expirable_content');
      $configuration = [
        'table' => $entity_type->getRevisionDataTable(),
        'field' => 'content_entity_revision_id',
        'left_table' => $table_alias,
        'left_field' => $left_entity_type->getKey('revision'),
        'extra' => [
          [
            'field' => 'content_entity_type_id',
            'value' => $left_entity_type->id(),
          ],
          [
            'field' => 'content_entity_id',
            'left_field' => $left_entity_type->getKey('id'),
          ],
        ],
      ];
      if ($left_entity_type->isTranslatable()) {
        $configuration['extra'][] = [
          'field' => $entity_type->getKey('langcode'),
          'left_field' => $left_entity_type->getKey('langcode'),
        ];
      }
      $join = Views::pluginManager('join')->createInstance('standard', $configuration);
      $this->tableAlias = $this->query->addRelationship('expirable_content', $join, 'expirable_content_field_revision');
    }

    return $this->tableAlias;
  }

}
