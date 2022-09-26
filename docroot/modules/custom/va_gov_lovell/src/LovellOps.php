<?php

namespace Drupal\va_gov_lovell;

use Drupal\node\NodeInterface;

/**
 * Wrapper class of largely static helper functions related to Lovell.
 */
class LovellOps {
  const TRICARE_ID = '1039';
  const TRICARE_VALUE = 'tricare';
  const VA_ID = '1040';
  const VA_VALUE = 'va';
  const BOTH_ID = '347';
  const BOTH_VALUE = 'both';
  const LOVELL_SECTIONS = [
    self::VA_ID => self::VA_VALUE,
    self::TRICARE_ID => self::TRICARE_VALUE,
    self::BOTH_ID => self::BOTH_VALUE,
  ];

  /**
   * Get the Lovell type of the node based on section.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity to check.
   *
   * @return string
   *   va, tricare, both or '' if not Lovell at all.
   */
  public static function getLovellType(NodeInterface $node) {
    $type = '';
    // If the current node is Lovell content grab the type.
    if ($node->hasField('field_administration')) {
      $section_id = $node->get('field_administration')->target_id;
      if (!empty(self::LOVELL_SECTIONS[$section_id])) {
        $type = self::LOVELL_SECTIONS[$section_id];
      }
    }
    return $type;
  }

  /**
   * Generates a unique index and sorts by it.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement $tree
   *   The menu link tree to manipulate.
   * @param string $lovell_type
   *   The type of Lovell page this menu appears upon.
   * @param array $menu_items
   *   An array of loaded menu items.
   *
   * @return \Drupal\Core\Menu\MenuLinkTreeElement
   *   The manipulated menu link tree.
   */
  public static function reduceLovellMenu(MenuLinkTreeElement $tree, $lovell_type, array $menu_items = NULL) {
    $new_tree = [];
    if (empty($menu_items)) {
      $menu_items = "//load all the menu items.";
    }

    foreach ($tree as $key => $v) {
      if ($tree[$key]->subtree) {
        $tree[$key]->subtree = self::reduceLovellMenu($tree[$key]->subtree, $lovell_type, $menu_items);
      }
      $instance = $tree[$key]->link;
      // Need to look for field_menu_section.
      // $new_tree[ $instance->getTitle()] = $tree[$key];.
    }

    return $tree;
  }

}
