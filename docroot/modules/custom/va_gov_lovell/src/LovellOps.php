<?php

namespace Drupal\va_gov_lovell;

use Drupal\node\NodeInterface;

/**
 * Wrapper class of largely static helper functions related to Lovell.
 */
class LovellOps {
  const BOTH_ID = '347';
  const BOTH_VALUE = 'both';
  const TRICARE_ID = '1039';
  const TRICARE_VALUE = 'tricare';
  const VA_ID = '1040';
  const VA_VALUE = 'va';
  const LOVELL_FEDERAL_PATH = '/lovell-federal-health-care';
  const LOVELL_MENU_ID = 'lovell-federal-health-care';
  const LOVELL_SECTIONS = [
    self::VA_ID => self::VA_VALUE,
    self::TRICARE_ID => self::TRICARE_VALUE,
    self::BOTH_ID => self::BOTH_VALUE,
  ];

  /**
   * Overrides the menu name if system path matches Lovell.
   *
   * @param string $system_path
   *   The system path to check.
   * @param string $menu_id
   *   The existing menu ID in case we need to pass it through & chain these.
   *
   * @return string
   *   The menu id.
   */
  public static function getLovellMenuFallback($system_path, $menu_id) {
    if ($system_path === self::LOVELL_FEDERAL_PATH) {
      $menu_id = self::LOVELL_MENU_ID;
    }
    return $menu_id;
  }

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

}
