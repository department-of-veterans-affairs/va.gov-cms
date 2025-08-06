<?php

namespace Drupal\va_gov_graphql\Plugin\GraphQL\Fields;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\node\NodeInterface;
use Drupal\va_gov_graphql\Service\NodeRelationshipAnalyzer;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * GraphQL field resolver for node relationships.
 *
 * @GraphQLField(
 *   id = "node_relationships",
 *   type = "NodeRelationshipInfo",
 *   name = "relationships",
 *   nullable = true,
 *   multi = false,
 *   secure = true,
 *   parents = { "NodeInterface" }
 * )
 */
class NodeRelationships extends FieldPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The node relationship analyzer service.
   *
   * @var \Drupal\va_gov_graphql\Service\NodeRelationshipAnalyzer
   */
  protected $relationshipAnalyzer;

  /**
   * The node relationship analyzer service.
   *
   * @var \Drupal\va_gov_graphql\Service\NodeRelationshipAnalyzer
   */
  protected $relationshipAnalyzer;

  /**
   * Constructs a NodeRelationships plugin.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\va_gov_graphql\Service\NodeRelationshipAnalyzer $relationship_analyzer
   *   The node relationship analyzer service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    NodeRelationshipAnalyzer $relationship_analyzer
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->relationshipAnalyzer = $relationship_analyzer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('va_gov_graphql.node_relationship_analyzer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if (!($value instanceof NodeInterface)) {
      return;
    }

    $relationships = $this->relationshipAnalyzer->getNodeRelationships($value);
    yield $relationships;
  }

}
