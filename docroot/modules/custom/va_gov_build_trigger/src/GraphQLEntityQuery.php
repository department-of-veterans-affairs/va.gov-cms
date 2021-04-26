<?php

namespace Drupal\va_gov_build_trigger;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql_core\Plugin\GraphQL\Fields\EntityQuery\EntityQuery;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Override the Default EntityQuery class.
 *
 * Overridden to remove node_list cache tags.
 */
class GraphQLEntityQuery extends EntityQuery {

  /**
   * {@inheritdoc}
   */
  protected function getCacheDependencies(array $result, $value, array $args, ResolveContext $context, ResolveInfo $info) {
    $entityType = $this->getEntityType($value, $args, $context, $info);
    $type = $this->entityTypeManager->getDefinition($entityType);

    $metadata = new CacheableMetadata();
    $metadata->addCacheContexts($type->getListCacheContexts());

    return [$metadata];
  }

}
