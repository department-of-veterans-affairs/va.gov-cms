<?php

namespace Drupal\va_gov_graphql\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * GraphQL field for entity type in relationship.
 *
 * @GraphQLField(
 *   id = "entity_relationship_type",
 *   type = "String",
 *   name = "entityType",
 *   nullable = true,
 *   multi = false,
 *   secure = true,
 *   parents = { "EntityRelationship" }
 * )
 */
class EntityRelationshipType extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if (!is_array($value) || !isset($value['entityType'])) {
      return;
    }

    yield $value['entityType'];
  }

}
