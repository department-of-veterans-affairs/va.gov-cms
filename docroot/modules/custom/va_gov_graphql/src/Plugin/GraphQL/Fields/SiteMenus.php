<?php

namespace Drupal\va_gov_graphql\Plugin\GraphQL\Fields;

/**
 * Returns a list of the machine names of all the menus on the site.
 *
 * @GraphQLField(
 *   id = "site_menus",
 *   type = "String",
 *   name = "siteMenus",
 *   nullable = true,
 *   multi = true,
 * )
 */

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * SiteMenus Class Doc Comment.
 *
 * @category Class
 * @package Site Menu
 * @author VA
 */
class SiteMenus extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    // Get custom and system menus in Drupal.
    $menus_array = menu_ui_get_menus(TRUE);

    // Return an array of drupal menu machine names.
    yield 'test';
  }

}
