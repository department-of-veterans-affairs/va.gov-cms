<?php

namespace Drupal\va_gov_graphql\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * GraphQL field for entities that reference a node.
 *
 * @GraphQLField(
 *   id = "node_relationship_referenced_by",
 *   type = "EntityRelationship",
 *   name = "referencedBy",
 *   nullable = true,
 *   multi = true,
 *   secure = true,
 *   parents = { "NodeRelationshipInfo" }
 * )
 */
class NodeRelationshipReferencedBy extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if (!is_array($value) || !isset($value['referencedBy'])) {
      return;
    }

    foreach ($value['referencedBy'] as $reference) {
      yield $reference;
    }
  }

}
