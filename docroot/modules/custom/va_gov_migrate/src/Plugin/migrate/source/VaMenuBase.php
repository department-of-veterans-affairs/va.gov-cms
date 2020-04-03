<?php

namespace Drupal\va_gov_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;

/**
 * Base class for migrating menus.
 */
abstract class VaMenuBase extends SourcePluginBase {

  /**
   * Sections.
   *
   * @var mixed
   */
  protected $sections;

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['id']['type'] = 'string';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields['href'] = 'Link Path';
    $fields['title'] = 'Link Title';
    $fields['external'] = 'External Path';
    $fields['weight'] = 'Weight';
    $fields['parent_id'] = 'Parent Id';

    return $fields;
  }

  /**
   * Turn array into a flat array of menu items for migration.
   *
   * @param array $menus
   *   Array of hierarchical menus to process.
   *
   * @return array
   *   The resulting array.
   */
  protected function process(array &$menus) {
    $this->setIds($menus);
    $this->setWeights($menus);
    return $this->flattenMenu($menus);
  }

  /**
   * Remove or repair domains in menu links.
   *
   * @param string $href
   *   The url to check.
   *
   * @return mixed
   *   The sanitized url.
   */
  abstract protected function sanitizeDomain($href);

  /**
   * Add weights to menu items based on their order in the array.
   *
   * @param array $menu_tree
   *   The array of menu items.
   */
  protected function setWeights(array &$menu_tree) {
    $relative_weight = 0;
    foreach ($menu_tree as &$item) {
      $item['weight'] = -50 + $relative_weight;
      $relative_weight++;
      if (!empty($item['items'])) {
        $this->setWeights($item['items']);
      }
    }
  }

  /**
   * Create a unique id for each menu item.
   *
   * The ids are used in post processing to set parent items.
   *
   * @param array $menu_tree
   *   The menu tree to set ids on.
   * @param string $parent_id
   *   The id of the current menu tree's parent.
   */
  protected function setIds(array &$menu_tree, $parent_id = '') {
    foreach ($menu_tree as $index => &$item) {
      $item['id'] = $parent_id . '-' . $index;
      if (!empty($item['items'])) {
        $this->setIds($item['items'], $item['id']);
      }
    }
  }

  /**
   * Transform a menu tree into a flat menu with parent href set for children.
   *
   * Also clean up the hrefs and set 'external'.
   *
   * @param array $menu_tree
   *   The tree to transform.
   * @param string $parent_id
   *   The parent id, if any.
   * @param string $parent_menu
   *   The parent menu name (will be filled in during recursion from top item).
   *
   * @return array
   *   A one-dimensional array of menu items.
   */
  protected function flattenMenu(array $menu_tree, $parent_id = '', $parent_menu = '') {
    $flat_menu = [];
    foreach ($menu_tree as $index => $item) {
      if (empty($item['href'])) {
        $item['href'] = 'route:<nolink>';
        $item['external'] = 0;
      }
      elseif (parse_url($item['href'], PHP_URL_SCHEME)) {
        $item['href'] = $this->sanitizeDomain($item['href']);
        $item['external'] = 1;
      }
      else {
        $item['href'] = rtrim($item['href'], '/');
        $item['external'] = 0;
      }

      if (!empty($parent_menu)) {
        $item['menu'] = $parent_menu;
      }
      $item['parent_id'] = $parent_id;
      $flat_menu[] = $item;

      if (!empty($item['items'])) {
        $new_parent = '';
        if (!empty($item['menu'])) {
          $new_parent = $item['menu'];
        }
        $flat_menu = array_merge($flat_menu, $this->flattenMenu($item['items'], $item['id'], $new_parent));
      }
      unset($item['items']);

    }

    return $flat_menu;
  }

  /**
   * Takes two page menus and merges them into one.
   *
   * Assumes that no two menu items have the same title & link.
   *
   * @param array $menu
   *   Array of menu trees.
   * @param array $menu2
   *   Another array of menu trees.
   *
   * @return array
   *   The consolidated menu tree.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  protected function mergeMenus(array $menu, array $menu2) {
    $merge_menu = $menu2;

    foreach ($menu as $menu_item) {
      $found = FALSE;
      foreach ($merge_menu as &$merge_item) {
        if ($menu_item['title'] == $merge_item['title'] &&
          ((empty($menu_item['href']) && empty($merge_item['href'])) ||
            $menu_item['href'] == $merge_item['href'])) {
          if (!empty($menu_item['items'])) {
            if (empty($merge_item['items'])) {
              $merge_item['items'] = $menu_item['items'];
            }
            else {
              $merge_item['items'] = $this->mergeMenus($menu_item['items'], $merge_item['items']);
            }
          }
          $found = TRUE;
          break;
        }
      }
      if (!$found) {
        $merge_menu[] = $menu_item;
      }
    }

    return $merge_menu;
  }

  /**
   * Find the parent item for submenus and merge them in.
   *
   * @param array $page
   *   The page structure containing the submenu.
   * @param array $menu
   *   The full menu to merge the submenus into.
   *
   * @return bool
   *   True if a parent item was found.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  protected function findMergeMenus(array $page, array &$menu) {
    $page_link = $page['backLink']['href'];
    foreach ($menu as &$menu_item) {
      if (!empty($menu_item['href']) && $menu_item['href'] == $page_link) {
        if (empty($menu_item['items'])) {
          $menu_item['items'] = $page['items'];
        }
        else {
          $menu_item['items'] = $this->mergeMenus($page['items'], $menu_item['items']);
        }
        return TRUE;
      }
      if (!empty($menu_item['items'])) {
        if ($this->findMergeMenus($page, $menu_item['items'])) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

}
