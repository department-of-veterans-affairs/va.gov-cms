<?php

namespace Drupal\va_gov_graphql\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Returns a list of the machine names of all the menus on the site.
 *
 * @GraphQLField(
 *   id = "site_menus",
 *   type = "String",
 *   name = "siteMenus",
 *   nullable = true,
 *   multi = true,
 *   secure = true,
 * )
 */
class SiteMenus extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    $menus = va_gov_graphql_get_menus();

    // Returns an array of drupal menu machine names.
    foreach ($menus as $key => $value) {
      if (strpos($key, 'bene') !== FALSE) {
        yield $key;
      }
    }
  }

}
