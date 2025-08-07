<?php

namespace Drupal\va_gov_graphql\Service;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\NodeInterface;

/**
 * Service for analyzing node relationships and dependencies.
 */
class NodeRelationshipAnalyzer {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs a NodeRelationshipAnalyzer object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityFieldManagerInterface $entity_field_manager,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->logger = $logger_factory->get('va_gov_graphql');
  }

  /**
   * Get comprehensive relationship data for a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to analyze.
   *
   * @return array
   *   Array containing references and referencedBy data.
   */
  public function getNodeRelationships(NodeInterface $node): array {
    return [
      'references' => $this->getReferencedEntities($node),
      'referencedBy' => $this->getReferencingEntities($node),
    ];
  }

  /**
   * Get all entities referenced by this node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to analyze.
   *
   * @return array
   *   Array of entity reference information.
   */
  public function getReferencedEntities(NodeInterface $node): array {
    $references = [];

    if (!($node instanceof FieldableEntityInterface)) {
      return $references;
    }

    foreach ($node->getFields() as $field_name => $field) {
      if ($field instanceof EntityReferenceFieldItemListInterface && !$field->isEmpty()) {
        $field_definition = $field->getFieldDefinition();
        $target_type = $field_definition->getSetting('target_type');

        foreach ($field->referencedEntities() as $referenced_entity) {
          if ($referenced_entity instanceof EntityInterface) {
            $references[] = [
              'id' => $referenced_entity->id(),
              'title' => $this->getEntityLabel($referenced_entity),
              'entityType' => $referenced_entity->getEntityTypeId(),
              'entityBundle' => $referenced_entity->bundle(),
              'fieldName' => $field_name,
              'fieldLabel' => $field_definition->getLabel(),
              'targetType' => $target_type,
            ];
          }
        }
      }
    }

    return $references;
  }

  /**
   * Get all entities that reference this entity (works for any entity type).
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to analyze.
   * @param array $options
   *   Options for the query (e.g., limit, entity_types).
   *
   * @return array
   *   Array of entity reference information.
   */
  public function getReferencingEntities($entity, array $options = []): array {
    $references = [];
    $limit = $options['limit'] ?? 50;
    $allowed_entity_types = $options['entity_types'] ?? NULL;

    // Get all entity reference fields across all entity types.
    $field_map = $this->entityFieldManager->getFieldMapByFieldType('entity_reference');

    foreach ($field_map as $entity_type_id => $fields) {
      // Skip if we're limiting to specific entity types.
      if ($allowed_entity_types && !in_array($entity_type_id, $allowed_entity_types)) {
        continue;
      }

      try {
        $storage = $this->entityTypeManager->getStorage($entity_type_id);
        
        foreach ($fields as $field_name => $field_info) {
          // Check if this field can reference the target entity type
          $can_reference_entity = FALSE;
          $field_definition = NULL;
          
          foreach ($field_info['bundles'] as $bundle) {
            $field_definitions = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
            $field_definition = $field_definitions[$field_name] ?? NULL;
            
            if ($field_definition && $field_definition->getSetting('target_type') === $entity->getEntityTypeId()) {
              $can_reference_entity = TRUE;
              break;
            }
          }
          
          if (!$can_reference_entity) {
            continue;
          }

          // Query entities that reference this entity.
          $query = $storage->getQuery()
            ->accessCheck(TRUE)
            ->condition($field_name, $entity->id())
            ->range(0, $limit);

          $entity_ids = $query->execute();

          if (!empty($entity_ids)) {
            $entities = $storage->loadMultiple($entity_ids);
            
            foreach ($entities as $entity) {
              if ($entity instanceof EntityInterface) {
                $references[] = [
                  'id' => $entity->id(),
                  'title' => $this->getEntityLabel($entity),
                  'entityType' => $entity->getEntityTypeId(),
                  'entityBundle' => $entity->bundle(),
                  'fieldName' => $field_name,
                  'fieldLabel' => $field_definition->getLabel(),
                ];
              }
            }
          }
        }
      }
      catch (\Exception $e) {
        $this->logger->error('Error querying references for entity type @entity_type: @message', [
          '@entity_type' => $entity_type_id,
          '@message' => $e->getMessage(),
        ]);
      }
    }

    return $references;
  }

  /**
   * Get a count of affected entities when a node is updated.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to analyze.
   *
   * @return array
   *   Array with counts by entity type.
   */
  public function getAffectedEntityCounts(NodeInterface $node): array {
    $relationships = $this->getNodeRelationships($node);
    $counts = [];

    foreach (['references', 'referencedBy'] as $relationship_type) {
      foreach ($relationships[$relationship_type] as $entity_data) {
        $entity_type = $entity_data['entityType'];
        $counts[$relationship_type][$entity_type] = ($counts[$relationship_type][$entity_type] ?? 0) + 1;
      }
    }

    return $counts;
  }

  /**
   * Get entity label safely.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return string
   *   The entity label or a fallback.
   */
  protected function getEntityLabel(EntityInterface $entity): string {
    try {
      $label = $entity->label();
      return $label ?: ($entity->getEntityTypeId() . ':' . $entity->id());
    }
    catch (\Exception $e) {
      return $entity->getEntityTypeId() . ':' . $entity->id();
    }
  }

}
