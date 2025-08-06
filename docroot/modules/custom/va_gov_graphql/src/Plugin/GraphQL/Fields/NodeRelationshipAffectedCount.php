<?php

namespace Drupal\va_gov_graphql\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * GraphQL field for affected nodes count.
 *
 * @GraphQLField(
 *   id = "node_relationship_affected_count",
 *   type = "Int",
 *   name = "affectedCount",
 *   nullable = true,
 *   multi = false,
 *   secure = true,
 *   parents = { "NodeRelationshipInfo" }
 * )
 */
class NodeRelationshipAffectedCount extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if (!is_array($value)) {
      return;
    }

    $count = 0;
    if (isset($value['references'])) {
      $count += count($value['references']);
    }
    if (isset($value['referencedBy'])) {
      $count += count($value['referencedBy']);
    }

    yield $count;
  }

}
