<?php

namespace Drupal\va_gov_graphql\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * GraphQL field for entities referenced by a node.
 *
 * @GraphQLField(
 *   id = "node_relationship_references",
 *   type = "EntityRelationship",
 *   name = "references",
 *   nullable = true,
 *   multi = true,
 *   secure = true,
 *   parents = { "NodeRelationshipInfo" }
 * )
 */
class NodeRelationshipReferences extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if (!is_array($value) || !isset($value['references'])) {
      return;
    }

    foreach ($value['references'] as $reference) {
      yield $reference;
    }
  }

}
