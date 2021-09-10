<?php

namespace Drupal\va_gov_graphql\Plugin\GraphQL\Fields;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Returns a list of the machine names of all the menus on the site.
 *
 * @GraphQLField(
 *   id = "moderation_state",
 *   type = "String",
 *   name = "moderationState",
 *   nullable = true,
 *   multi = false,
 *   secure = true,
 *   parents = { "Entity" },
 * )
 */
class ModerationState extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof FieldableEntityInterface && $value->hasField('moderation_state')) {
      yield $value->get('moderation_state')->value;
    }
  }

}
