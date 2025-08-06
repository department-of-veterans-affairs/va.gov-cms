<?php

namespace Drupal\va_gov_graphql\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * GraphQL field for entity ID in relationship.
 *
 * @GraphQLField(
 *   id = "entity_relationship_id",
 *   type = "String",
 *   name = "id",
 *   nullable = true,
 *   multi = false,
 *   secure = true,
 *   parents = { "EntityRelationship" }
 * )
 */
class EntityRelationshipId extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if (!is_array($value) || !isset($value['id'])) {
      return;
    }

    yield $value['id'];
  }

}
