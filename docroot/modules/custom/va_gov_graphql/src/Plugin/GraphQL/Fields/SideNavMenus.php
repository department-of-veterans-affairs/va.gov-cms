<?php

namespace Drupal\va_gov_graphql\Plugin\GraphQL\Fields;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Returns a list of the machine names of all the system menus on the site.
 *
 * @GraphQLField(
 *   id = "side_nav_menus",
 *   type = "String",
 *   name = "sideNavMenus",
 *   nullable = true,
 *   multi = true,
 *   secure = true,
 * )
 */
class SideNavMenus extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    $menus_array = menu_ui_get_menus(FALSE);
    $systems_menus = [];
    // Returns an array of drupal menu machine names.
    foreach ($menus_array as $key => $value) {
      if (strpos($key, 'va') !== FALSE || strpos($key, 'pittsburgh-health-care') !== FALSE) {
        yield $key;
      }
    }
  }

}
