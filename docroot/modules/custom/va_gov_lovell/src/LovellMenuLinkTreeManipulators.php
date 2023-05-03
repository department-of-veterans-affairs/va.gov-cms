<?php

namespace Drupal\va_gov_lovell;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\va_gov_lovell\LovellOps; // phpcs:ignore

/**
 * Provides a menu tree manipulator for VAMC Lovell.
 */
class LovellMenuLinkTreeManipulators {


  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * Menu entities for all menu items in the menu.
   *
   * @var array
   */
  protected $menuEntities = [];

  /**
   * Menu Lovell type for all menu items in the menu.
   *
   * @var string
   */
  protected $menuType = '';

  /**
   * Constructs a Drupal\va_gov_lovell\LovellMenuLinkTreeManipulators object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Removes Lovell instances that do not match the Lovell flavor.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement[] $tree
   *   The menu link tree to manipulate.
   * @param string $lovell_type
   *   The type of Lovell page this menu appears upon.
   * @param bool $firstrun
   *   Used to flag if this was/was not the first run of this recurive function.
   *
   * @return \Drupal\Core\Menu\MenuLinkTreeElement[]
   *   The manipulated menu link tree.
   */
  public function reduceLovellMenu(array $tree, $lovell_type, $firstrun = TRUE) {
    $lovell_type = ($lovell_type === LovellOps::BOTH_VALUE) ? LovellOps::VA_VALUE : $lovell_type;
    $tricare_menu = [];
    $va_menu = [];

    if ($firstrun && !empty($tree)) {
      // If the system parent is both, default to VA.
      $this->menuType = $lovell_type;
      $this->setMenuEntities($tree);
      // Capture the main menus for juggling.
      // This bit is pretty fragile because it depends on order.
      $tricare_menu = array_shift($tree);
      $va_menu = array_shift($tree);
      $va_both_menu = array_shift($tree);
      $tree = $va_both_menu->subtree;
      unset($va_both_menu);
    }

    foreach ($tree as $key => $element) {
      if ($tree[$key]->subtree) {
        // There is a subtree.  Begin recursion.
        $tree[$key]->subtree = $this->reduceLovellMenu($tree[$key]->subtree, $lovell_type, FALSE);
      }
      $link = $element->link;
      $metadata = $link->getMetaData();
      $mid = $metadata['entity_id'] ?? '';

      if ($this->shouldRemove($mid)) {
        unset($tree[$key]);
      }
    }

    if ($firstrun && !empty($tree)) {
      if ($lovell_type === LovellOps::TRICARE_VALUE) {
        $tricare_menu->subtree = $tree;
        $va_menu->subtree = [];
        $tree = [
          '1' => $va_menu,
          '2' => $tricare_menu,
        ];
      }
      elseif ($lovell_type === LovellOps::VA_VALUE || $lovell_type === LovellOps::BOTH_VALUE) {
        $va_menu->subtree = $tree;
        $tricare_menu->subtree = [];
        $tree = [
          '1' => $tricare_menu,
          '2' => $va_menu,
        ];
      }
    }
    return $tree;
  }

  /**
   * Loads and sets the menu entities.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement[] $tree
   *   The menu link tree to manipulate.
   */
  protected function setMenuEntities(array $tree) {
    if (empty($this->menuEntities)) {
      $ids = $this->getIdsFromTree($tree);
      if (!empty($ids) && is_array($ids)) {
        $this->menuEntities = $this->entityTypeManager->getStorage('menu_link_content')->loadMultiple($ids);
      }
    }
  }

  /**
   * Traverse a menu tree and extract all menu entity ids.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement[] $tree
   *   An array of MenuLinkTreeElements.
   *
   * @return array
   *   An array of menu ids accumulated from all recursion of tree.
   */
  protected function getIdsFromTree(array $tree): array {
    $ids = [];
    foreach ($tree as $branch) {
      $menu_item = $this->accessProtected($branch->link, 'entity');
      $ids[] = $menu_item->id();
      if (!empty($branch->subtree)) {
        $ids = array_merge($ids, $this->getIdsFromTree($branch->subtree));
      }
    }
    return $ids;
  }

  /**
   * Checks if a menu item belongs via mid lookup.
   *
   * @param string $mid
   *   The menu id to lookup.
   *
   * @return bool
   *   TRUE if the element should be removed.  FALSE otherwise.
   */
  protected function shouldRemove($mid) {
    $menu_item_location = $this->getMenuSectionField($mid);
    if (
      $menu_item_location === $this->menuType ||
      $menu_item_location === LovellOps::BOTH_VALUE
      ) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Retrieves the  value of field_content_section from the menu item.
   *
   * @param string $mid
   *   Menu id to lookup.
   *
   * @return string
   *   The value of menu->field_menu_section.
   */
  protected function getMenuSectionField($mid) {
    $lovell_type = '';
    if (!empty($this->menuEntities[$mid])) {
      $lovell_type = $this->menuEntities[$mid]->field_menu_section->value;
    }
    return $lovell_type;
  }

  /**
   * Retrieves a property from a class even if it is protected.
   *
   * @param object $object
   *   The haystack that contains needle (lookup_property).
   * @param string $lookup_property
   *   The property to be returned.
   *
   * @return mixed
   *   Whatever is stored in the property of $object.
   */
  protected function accessProtected($object, $lookup_property) {
    if (is_object($object)) {
      $reflection = new \ReflectionClass($object);
      $property = $reflection->getProperty($lookup_property);
      $property->setAccessible(TRUE);
      return $property->getValue($object);
    }
    return NULL;
  }

}
