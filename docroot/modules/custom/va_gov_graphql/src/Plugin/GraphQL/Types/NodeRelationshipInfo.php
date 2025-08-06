<?php

namespace Drupal\va_gov_graphql\Plugin\GraphQL\Types;

use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;

/**
 * GraphQL type for node relationship information.
 *
 * @GraphQLType(
 *   id = "node_relationship_info",
 *   name = "NodeRelationshipInfo",
 *   description = "Information about a node's relationships with other entities"
 * )
 */
class NodeRelationshipInfo extends TypePluginBase {

}
