<?php

namespace Drupal\va_gov_graphql\Commands;

use Drupal\node\Entity\Node;
use Drupal\va_gov_graphql\Service\NodeRelationshipAnalyzer;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for analyzing node relationships.
 */
class NodeRelationshipCommands extends DrushCommands {

  /**
   * The node relationship analyzer service.
   *
   * @var \Drupal\va_gov_graphql\Service\NodeRelationshipAnalyzer
   */
  protected $relationshipAnalyzer;

  /**
   * Constructs a NodeRelationshipCommands object.
   *
   * @param \Drupal\va_gov_graphql\Service\NodeRelationshipAnalyzer $relationship_analyzer
   *   The node relationship analyzer service.
   */
  public function __construct(NodeRelationshipAnalyzer $relationship_analyzer) {
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
    $node = Node::load($nid);
    
    if (!$node) {
      $this->io()->error("Node {$nid} not found.");
      return;
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
    $node = Node::load($nid);
    
    if (!$node) {
      $this->io()->error("Node {$nid} not found.");
      return;
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

}
