<?php

namespace Drupal\va_gov_lovell;

use Drupal\node\NodeInterface;
use Drupal\va_gov_backend\Service\VaGovUrlInterface;

/**
 * Wrapper class of largely static helper functions related to Lovell.
 */
class LovellOps {
  const BOTH_ID = '347';
  const BOTH_NAME = 'Lovell Federal health care';
  const BOTH_PATH = 'lovell-federal-health-care';
  const BOTH_VALUE = 'both';
  const TRICARE_ID = '1039';
  const TRICARE_NAME = 'Lovell Federal health care - TRICARE';
  const TRICARE_PATH = 'lovell-federal-health-care-tricare';
  const TRICARE_VALUE = 'tricare';
  const VA_ID = '1040';
  const VA_NAME = 'Lovell Federal health care - VA';
  const VA_PATH = 'lovell-federal-health-care-va';
  const VA_VALUE = 'va';
  const LOVELL_MENU_ID = 'lovell-federal-health-care';
  const LOVELL_SECTIONS = [
    self::VA_ID => self::VA_VALUE,
    self::TRICARE_ID => self::TRICARE_VALUE,
    self::BOTH_ID => self::BOTH_VALUE,
  ];

  /**
   * Build the array of Lovell URLs.
   *
   * @param string $section_id
   *   An id for a section taxonomy term.
   * @param \Drupal\va_gov_backend\Service\VaGovUrlInterface $vaGovUrl
   *   The va.gov URL service.
   * @param \Drupal\node\NodeInterface $node
   *   Node entity to query for its original path.
   *
   * @return array
   *   Array of complete Lovell URLs.
   */
  public static function buildArrayLovellUrls(string $section_id, VaGovUrlInterface $vaGovUrl, NodeInterface $node): array {
    $va_gov_urls_to_display = [];
    $valid_prefixes = LovellOps::getValidPrefixes($section_id);
    foreach ($valid_prefixes as $prefix) {
      $va_gov_url_front_end_domain = $vaGovUrl->getVaGovFrontEndUrl();
      $va_gov_urls_to_display[] = LovellOps::buildLovellUrlWithCorrectPrefix($node, $prefix, $va_gov_url_front_end_domain);
    }
    return $va_gov_urls_to_display;
  }

  /**
   * Build the Lovell URL to the front end with the correct prefix.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node entity to query for its original path.
   * @param string $prefix
   *   The prefix of the path to use, e.g. "lovell-federal-tricare-health-care",
   *   "lovell-federal-va-health-care", or "lovell-federal-health-care".
   * @param string $va_gov_url_front_end_domain
   *   The front-end URL domain.
   *
   * @return string
   *   The complete Lovell URL.
   */
  public static function buildLovellUrlWithCorrectPrefix(NodeInterface $node, string $prefix, string $va_gov_url_front_end_domain): string {
    $url = "";
    $original_path = $node->toUrl()->toString();
    $pattern_to_trim = "/^\/([a-z]|\-)*/i";
    $trimmed_path = preg_replace($pattern_to_trim, "/" . $prefix, $original_path);
    $url = $va_gov_url_front_end_domain . $trimmed_path;
    return $url;
  }

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
    $system_path = ltrim($system_path, '/');
    if ($system_path === self::BOTH_PATH) {
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

  /**
   * Generate valid lovell url prefixes for the provided section.
   *
   * @param string $section_id
   *   An id for a section taxonomy term.
   *
   * @return array
   *   An array of valid url prefixes .
   */
  public static function getValidPrefixes(string $section_id): array {
    // Define valid url prefixes for Lovell content.
    $valid_prefixes = [
      LovellOps::TRICARE_ID => LovellOps::TRICARE_PATH,
      LovellOps::VA_ID => LovellOps::VA_PATH,
    ];

    // If section is not both remove invalid prefixes.
    if ($section_id !== LovellOps::BOTH_ID) {
      $valid_prefixes = array_intersect_key($valid_prefixes, [$section_id => 'keep']);
    }

    return $valid_prefixes;
  }

  /**
   * Checks if the node is a Lovell Federal (both) listing page.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity to check.
   *
   * @return bool
   *   TRUE if it is a listing page assigned to Lovell Federal,
   *   FALSE otherwise.
   */
  public static function isLovellBothListingPage(NodeInterface $node): bool {
    $is = FALSE;
    if ($node->hasField('field_administration')) {
      $section_id = $node->get('field_administration')->target_id;
      $type = $node->getType();
      $listing_types = [
        'event_listing',
        'press_releases_listing',
        'story_listing',
      ];
      if (($section_id === LovellOps::BOTH_ID) && (in_array($type, $listing_types))) {
        $is = TRUE;
      }
    }
    return $is;
  }

}
