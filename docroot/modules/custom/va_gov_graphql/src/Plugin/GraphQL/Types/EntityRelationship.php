<?php

namespace Drupal\va_gov_graphql\Plugin\GraphQL\Types;

use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;

/**
 * GraphQL type for entity relationship data.
 *
 * @GraphQLType(
 *   id = "entity_relationship",
 *   name = "EntityRelationship",
 *   description = "Information about an entity that has a relationship to another entity"
 * )
 */
class EntityRelationship extends TypePluginBase {

}
