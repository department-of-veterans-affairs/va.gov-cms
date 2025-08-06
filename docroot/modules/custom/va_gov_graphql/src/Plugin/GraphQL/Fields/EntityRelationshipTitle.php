<?php

namespace Drupal\va_gov_graphql\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * GraphQL field for entity title in relationship.
 *
 * @GraphQLField(
 *   id = "entity_relationship_title",
 *   type = "String",
 *   name = "title",
 *   nullable = true,
 *   multi = false,
 *   secure = true,
 *   parents = { "EntityRelationship" }
 * )
 */
class EntityRelationshipTitle extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if (!is_array($value) || !isset($value['title'])) {
      return;
    }

    yield $value['title'];
  }

}
