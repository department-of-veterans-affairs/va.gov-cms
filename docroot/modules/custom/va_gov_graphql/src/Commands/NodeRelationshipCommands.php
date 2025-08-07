<?php

namespace Drupal\va_gov_graphql\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\va_gov_graphql\Service\NodeRelationshipAnalyzer;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for analyzing node relationships.
 */
class NodeRelationshipCommands extends DrushCommands {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The node relationship analyzer service.
   *
   * @var \Drupal\va_gov_graphql\Service\NodeRelationshipAnalyzer
   */
  protected $relationshipAnalyzer;

  /**
   * Constructs a NodeRelationshipCommands object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\va_gov_graphql\Service\NodeRelationshipAnalyzer $relationship_analyzer
   *   The node relationship analyzer service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, NodeRelationshipAnalyzer $relationship_analyzer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->relationshipAnalyzer = $relationship_analyzer;
  }

  /**
   * Analyze relationships for a given node.
   *
   * @param int $nid
   *   The node ID to analyze.
   *
   * @command va-gov-graphql:analyze-node
   * @aliases vagql:analyze,vagql:an
   * @usage va-gov-graphql:analyze-node 123
   *   Analyze relationships for node 123.
   */
  public function analyzeNode($nid) {
    try {
      $node = $this->entityTypeManager->getStorage('node')->load($nid);
      
      if (!$node) {
        $this->io()->error("Node {$nid} not found.");
        return DrushCommands::EXIT_FAILURE;
      }

      $this->io()->title("Node Relationship Analysis for: {$node->getTitle()} (ID: {$nid})");
      
      $relationships = $this->relationshipAnalyzer->getNodeRelationships($node);
      
      // Display entities this node references.
      $this->io()->section('Entities Referenced by This Node');
      if (!empty($relationships['references'])) {
        $rows = [];
        foreach ($relationships['references'] as $ref) {
          $rows[] = [
            $ref['id'],
            $ref['title'],
            $ref['entityType'],
            $ref['entityBundle'],
            $ref['fieldName'],
          ];
        }
        $this->io()->table(['ID', 'Title', 'Entity Type', 'Bundle', 'Field'], $rows);
      }
      else {
        $this->io()->text('No referenced entities found.');
      }

      // Display entities that reference this node.
      $this->io()->section('Entities That Reference This Node');
      if (!empty($relationships['referencedBy'])) {
        $rows = [];
        foreach ($relationships['referencedBy'] as $ref) {
          $rows[] = [
            $ref['id'],
            $ref['title'],
            $ref['entityType'],
            $ref['entityBundle'],
            $ref['fieldName'],
          ];
        }
        $this->io()->table(['ID', 'Title', 'Entity Type', 'Bundle', 'Field'], $rows);
      }
      else {
        $this->io()->text('No referencing entities found.');
      }

      // Display summary counts.
      $counts = $this->relationshipAnalyzer->getAffectedEntityCounts($node);
      $this->io()->section('Summary');
      $this->io()->text("Total entities referenced: " . count($relationships['references']));
      $this->io()->text("Total entities referencing this node: " . count($relationships['referencedBy']));
      
      if (!empty($counts)) {
        $this->io()->text("\nBreakdown by relationship type:");
        foreach ($counts as $type => $entity_counts) {
          $this->io()->text("  {$type}:");
          foreach ($entity_counts as $entity_type => $count) {
            $this->io()->text("    {$entity_type}: {$count}");
          }
        }
      }
    }
    catch (\Exception $e) {
      $this->io()->error("Error analyzing node relationships: " . $e->getMessage());
      return DrushCommands::EXIT_FAILURE;
    }
  }

  /**
   * Analyze relationships for any entity type.
   *
   * @param string $entity_type
   *   The entity type (e.g., node, taxonomy_term, media).
   * @param int $entity_id
   *   The entity ID to analyze.
   *
   * @command va-gov-graphql:analyze-entity
   * @aliases vagql:entity
   * @usage va-gov-graphql:analyze-entity taxonomy_term 226
   *   Analyze relationships for taxonomy term 226.
   * @usage va-gov-graphql:analyze-entity media 12345
   *   Analyze relationships for media entity 12345.
   */
  public function analyzeEntity($entity_type, $entity_id) {
    try {
      $entity = $this->entityTypeManager->getStorage($entity_type)->load($entity_id);
      
      if (!$entity) {
        $this->io()->error("{$entity_type} {$entity_id} not found.");
        return DrushCommands::EXIT_FAILURE;
      }

      $entity_label = method_exists($entity, 'label') ? $entity->label() : "{$entity_type}:{$entity_id}";
      $this->io()->title("Entity Relationship Analysis for: {$entity_label} (Type: {$entity_type}, ID: {$entity_id})");
      
      // For nodes, use the existing method, for others we need a different approach
      if ($entity_type === 'node') {
        $relationships = $this->relationshipAnalyzer->getNodeRelationships($entity);
        $this->displayRelationships($relationships);
      } else {
        // For non-node entities, we can still analyze what references them
        $this->io()->section('Entities That Reference This ' . ucfirst(str_replace('_', ' ', $entity_type)));
        $referencing = $this->relationshipAnalyzer->getReferencingEntities($entity, ['limit' => 100]);
        
        if (!empty($referencing)) {
          $rows = [];
          foreach ($referencing as $ref) {
            $rows[] = [
              $ref['id'],
              substr($ref['title'], 0, 50) . (strlen($ref['title']) > 50 ? '...' : ''),
              $ref['entityType'],
              $ref['entityBundle'],
              $ref['fieldName'],
            ];
          }
          $this->io()->table(['ID', 'Title', 'Entity Type', 'Bundle', 'Reference Field'], $rows);
          $this->io()->success("Found " . count($referencing) . " entities that reference this {$entity_type}.");
        } else {
          $this->io()->text('No entities reference this ' . $entity_type . '.');
        }
      }
    }
    catch (\Exception $e) {
      $this->io()->error("Error analyzing entity relationships: " . $e->getMessage());
      return DrushCommands::EXIT_FAILURE;
    }
  }

  /**
   * Find nodes that would be affected when a given node is updated.
   *
   * @param int $nid
   *   The node ID to check.
   * @param array $options
   *   Command options.
   *
   * @command va-gov-graphql:find-affected
   * @aliases vagql:affected,vagql:fa
   * @option limit Maximum number of results to return per entity type
   * @usage va-gov-graphql:find-affected 123
   *   Find all entities affected by changes to node 123.
   * @usage va-gov-graphql:find-affected 123 --limit=10
   *   Find up to 10 entities per type affected by changes to node 123.
   */
  public function findAffected($nid, array $options = ['limit' => 50]) {
    try {
      $node = $this->entityTypeManager->getStorage('node')->load($nid);
      
      if (!$node) {
        $this->io()->error("Node {$nid} not found.");
        return DrushCommands::EXIT_FAILURE;
      }

      $this->io()->title("Entities Affected by Changes to: {$node->getTitle()} (ID: {$nid})");
      
      $query_options = ['limit' => $options['limit']];
      $referencing = $this->relationshipAnalyzer->getReferencingEntities($node, $query_options);
      
      if (!empty($referencing)) {
        $rows = [];
        foreach ($referencing as $ref) {
          $rows[] = [
            $ref['id'],
            substr($ref['title'], 0, 50) . (strlen($ref['title']) > 50 ? '...' : ''),
            $ref['entityType'],
            $ref['entityBundle'],
            $ref['fieldName'],
          ];
        }
        $this->io()->table(['ID', 'Title', 'Entity Type', 'Bundle', 'Reference Field'], $rows);
        
        $this->io()->success("Found " . count($referencing) . " entities that reference this node.");
        
        if (count($referencing) >= $options['limit']) {
          $this->io()->note("Results limited to {$options['limit']} entities. Use --limit option to see more.");
        }
      }
      else {
        $this->io()->text('No entities reference this node.');
      }
    }
    catch (\Exception $e) {
      $this->io()->error("Error finding affected entities: " . $e->getMessage());
      return DrushCommands::EXIT_FAILURE;
    }
  }

  /**
   * Helper method to display relationship data.
   *
   * @param array $relationships
   *   The relationships array containing 'references' and 'referencedBy' keys.
   */
  protected function displayRelationships(array $relationships) {
    // Display entities this node references.
    $this->io()->section('Entities Referenced by This Node');
    if (!empty($relationships['references'])) {
      $rows = [];
      foreach ($relationships['references'] as $ref) {
        $rows[] = [
          $ref['id'],
          $ref['title'],
          $ref['entityType'],
          $ref['entityBundle'],
          $ref['fieldName'],
        ];
      }
      $this->io()->table(['ID', 'Title', 'Entity Type', 'Bundle', 'Field'], $rows);
    }
    else {
      $this->io()->text('No referenced entities found.');
    }

    // Display entities that reference this node.
    $this->io()->section('Entities That Reference This Node');
    if (!empty($relationships['referencedBy'])) {
      $rows = [];
      foreach ($relationships['referencedBy'] as $ref) {
        $rows[] = [
          $ref['id'],
          $ref['title'],
          $ref['entityType'],
          $ref['entityBundle'],
          $ref['fieldName'],
        ];
      }
      $this->io()->table(['ID', 'Title', 'Entity Type', 'Bundle', 'Field'], $rows);
    }
    else {
      $this->io()->text('No referencing entities found.');
    }

    // Display summary
    $this->io()->section('Summary');
    $this->io()->text("Total entities referenced: " . count($relationships['references']));
    $this->io()->text("Total entities referencing this node: " . count($relationships['referencedBy']));
  }

}
