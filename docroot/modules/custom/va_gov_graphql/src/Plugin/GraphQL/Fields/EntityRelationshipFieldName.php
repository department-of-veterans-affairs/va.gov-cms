<?php

namespace Drupal\va_gov_graphql\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * GraphQL field for relationship field name.
 *
 * @GraphQLField(
 *   id = "entity_relationship_field_name",
 *   type = "String",
 *   name = "fieldName",
 *   nullable = true,
 *   multi = false,
 *   secure = true,
 *   parents = { "EntityRelationship" }
 * )
 */
class EntityRelationshipFieldName extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if (!is_array($value) || !isset($value['fieldName'])) {
      return;
    }

    yield $value['fieldName'];
  }

}
