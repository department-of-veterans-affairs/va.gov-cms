<?php

namespace Drupal\va_gov_menu_access\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeForm;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\va_gov_user\Service\UserPermsService;

/**
 * Class MenuReductionService a service for reducing possible menu items.
 */
class MenuReductionService {

  use StringTranslationTrait;

  /**
   * Possible states for the type of menu item.
   */
  const ENABLED = 'enabled';
  const DISABLED = 'disabled';
  const SEPARATOR = 'separator';

  /**
   * The alias manager interface.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;


  /**
   * The form entry for the current menu parent.
   *
   * @var string
   */
  protected $currentMenuParent = '';

  /**
   * The entity type manager interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form_state interface.
   *
   * @var Drupal\Core\Form\FormStateInterface
   */
  protected $formState;


  /**
   * An array of rules built from config settings.
   *
   * @var array
   */
  protected $menuRules = [
    'universal_parent_menu_items' => [],
    'universal_locked_paths' => [],
    'single_locked_paths' => [],
  ];

  /**
   * The node bundle of the current node.
   *
   * @var string|null
   */
  protected $nodeBundle = NULL;

  /**
   * The menu parent options before reducing.
   *
   * @var array
   */
  protected $originalMenuParentOptions = [];

  /**
   * The settings from va_gov_menu_access.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $settings;

  /**
   * The va_gov_user_perms service.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected $userPermsService;

  /**
   * Constructor for the MenuReduction service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   Core path alias manager.
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   VaGov user perms service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, AliasManagerInterface $alias_manager, UserPermsService $user_perms_service) {
    $this->aliasManager = $alias_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->userPermsService = $user_perms_service;
    $this->settings = $config_factory->get('va_gov_menu_access.settings');
    $this->buildMenuAccessRules();
  }

  /**
   * Reduces a given menu based on user and allowed patterns.
   *
   * @param array $form
   *   The form array by reference.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Instance of FormStateInterface.
   */
  public function reduceForm(array &$form, FormStateInterface $form_state) {
    $this->formState = $form_state;
    $this->nodeBundle = ($this->formState->getFormObject() instanceof NodeForm) ? $this->formState->getFormObject()->getEntity()->getType() : NULL;
    $this->currentMenuParent = $this->formState->getValue([
      'menu',
      'menu_parent',
    ]);
    $this->originalMenuParentOptions = $form['menu']['link']['menu_parent']['#options'] ?? [];

    if ($this->userPermsService->hasAdminRole()) {
      // User is an admin so no menu reduction needed.
      $this->setEmptyMenuParentSelector($form);
      $this->nuke();
      return;
    }

    $form_id = $this->formState->getFormObject()->getFormId();
    // RISK: This is a risky pattern tying menu governance to page alias.
    // Only config joins menu and alias, a config change could break this.
    $page_alias = !empty($form['path']['widget'][0]['alias']['#default_value']) ? $form['path']['widget'][0]['alias']['#default_value'] : '';

    // Health care region detail pages are special because they have no
    // pattern based alias and need their own handling.
    if (($this->nodeBundle !== 'health_care_region_detail_page') || ($this->isLockedPath($page_alias))) {
      // We have a match, lock it down.
      $this->disableMenuOptions($form, $form_id);
    }

    $this->applyVamcMenuRulesForDetailPage($form);
    $this->setEmptyMenuParentSelector($form);
    $this->nuke();
  }

  /**
   * Checks if the menu has a parent set.
   *
   * @return bool
   *   TRUE if the menu has a parent, FALSE otherwise.
   */
  protected function hasMenuParent() : bool {
    return (!empty($this->formState->getValue(['menu', 'menu_parent']))) ? TRUE : FALSE;
  }

  /**
   * Checks if the menu is enabled.
   *
   * @return bool
   *   TRUE if the menu is enabled, FALSE otherwise.
   */
  protected function isMenuEnabled() : bool {
    return ($this->formState->getValue(['menu', 'enabled']) === 1) ? TRUE : FALSE;
  }

  /**
   * Returns TRUE if the current alias matches a config locked path.
   *
   * @return bool
   *   TRUE if a match, FALSE otherwise.
   */
  protected function isLockedPath($page_alias) : bool {
    $is_match = FALSE;
    if (empty($page_alias)) {
      // There is no alias to match.
      return $is_match;
    }
    // In this loop, we look to see if we have either an all children
    // pattern lock, or a specific parent pattern lock.
    foreach ($this->menuRules['universal_locked_paths'] as $path) {
      if (strpos($path, '*') !== FALSE) {
        $path_sanitized = rtrim($path, '*');
        if (strpos($page_alias, $path_sanitized) !== FALSE) {
          $is_match = TRUE;
          break;
        }
      }
      else {
        if (strpos($page_alias, $path) !== FALSE) {
          $path_sanitized = str_replace('/', '\/', $path);
          $matches = preg_match('/' . $path_sanitized . '$/', $page_alias);
          if ($matches > 0) {
            $is_match = TRUE;
            break;
          }
        }
      }
    }

    if (!$is_match && in_array($page_alias, $this->menuRules['single_locked_paths'])) {
      // The page is a match for the single locked paths.
      $is_match = TRUE;
    }

    return $is_match;
  }

  /**
   * Set the menu parent selector to the default empty if empty.
   *
   * @param array $form
   *   The form array by reference.
   */
  protected function setEmptyMenuParentSelector(array &$form) {
    if (!empty($form['menu']) && !empty($form['menu']['link']['menu_parent'])) {
      // Sets an empty Select prompt on parent menu selector for all node forms.
      $form['menu']['link']['menu_parent']['#options'] = array_merge(['0' => '- Select a value -'], $form['menu']['link']['menu_parent']['#options']);
      // If we don't have a value set, yet, ensure the Select appears first.
      if (!$this->isMenuEnabled() && $this->formState->getFormObject()->getEntity()->isNew()) {
        $form['menu']['link']['menu_parent']['#default_value'] = '0';
        $form['menu']['link']['menu_parent']['#value'] = '0';
      }
    }
  }

  /**
   * Applies menu rules specific to VAMCs.
   *
   * @param array $form
   *   The form array by reference.
   */
  protected function applyVamcMenuRulesForDetailPage(array &$form) {
    if ($this->nodeBundle !== 'health_care_region_detail_page') {
      // Not our type, bail out.
      return;
    }
    // List of menu items that aren't real links = faux menu items.
    $allowed_separators = ['About'];
    $allowed_parents = [];
    $enabled_count = 0;

    $parent_options_menu_ids = $this->extractMenuIds($form['menu']['link']['menu_parent']['#options']);
    $parent_menu_items = $this->loadParentMenuItems($parent_options_menu_ids);

    // Loop through them, and decide whether it should be allowed in options.
    foreach ($parent_menu_items as $key => $menu_item) {
      $alias = $this->getAliasFromUri($menu_item->get('link')->uri);
      if ($alias) {
        $subject_uuid = $parent_options_menu_ids[$menu_item->get('uuid')->value];
        $menu_element_type = $this->getMenuItemType($alias);
        $menu_element_type = $menu_element_type ?? $this->checkForSeparator($allowed_separators, $menu_item);

        $this->addAllowedParent($allowed_parents, $enabled_count, $menu_element_type, $subject_uuid);
      }
    }
    // Reassemble our options with items set to allow children in select list,
    // in original order.
    ksort($allowed_parents);
    $allowed_parents_formatted = [];
    foreach ($allowed_parents as $key => $allowed_sliced) {
      $allowed_parents_formatted[key($allowed_sliced)] = reset($allowed_sliced);
    }
    $form['menu']['link']['menu_parent']['#options'] = $allowed_parents_formatted;
    if ($enabled_count < 1) {
      $form['menu']['link']['menu_parent']['#attributes']['class'][] = 'no-available-menu-targets';
    }
    $this->preventUnintendedChangeOfParent($form);
  }

  /**
   * Loads and returns the entities for all menu parents.
   *
   * @param array $parent_options_menu_ids
   *   Menu ids.
   *
   * @return array
   *   An array of menu entities loaded.
   */
  protected function loadParentMenuItems(array $parent_options_menu_ids) {
    $parent_menu_items = [];
    if (!empty($parent_options_menu_ids)) {
      $parent_menu_items = $this->entityTypeManager
        ->getStorage('menu_link_content')
        ->loadByProperties([
          'uuid' => array_keys($parent_options_menu_ids),
        ]);
    }
    return $parent_menu_items;
  }

  /**
   * Getter for the alias of a node.
   *
   * @param string $uri
   *   The drupal URI.
   *
   * @return string
   *   The alias for the uri.
   */
  protected function getAliasFromUri($uri) {
    $alias = NULL;
    if (substr($uri, 0, 12) === 'entity:node/' || $uri === 'route:<nolink>') {
      // Get our alias, as well as last arg of alias for comparison.
      $uri_parts = explode('entity:node/', $uri);
      $nid = end($uri_parts);
      $alias = $this->aliasManager->getAliasByPath('/node/' . $nid);
    }
    return $alias;
  }

  /**
   * Adds an entry to the allowed_parents array if there is a type match.
   *
   * @param array $allowed_parents
   *   A list of allowed parent items by reference.
   * @param int $enabled_count
   *   A count of the enabled items, by reference.
   * @param null|string $menu_element_type
   *   The type of menu element.
   * @param array $subject_uuid
   *   Menu subject array.
   */
  protected function addAllowedParent(array &$allowed_parents, int &$enabled_count, $menu_element_type, array $subject_uuid) {
    // If we have menu type set, put it in the array.
    switch ($menu_element_type) {
      case self::ENABLED:
        $allowed_parents[$subject_uuid['key_count']][$subject_uuid['menu_key']] = $subject_uuid['option'];
        $enabled_count++;
        break;

      case self::DISABLED:
        $allowed_parents[$subject_uuid['key_count']][$subject_uuid['menu_key']] = "{$subject_uuid['option']} | Disabled";
        break;

      case self::SEPARATOR:
        $allowed_parents[$subject_uuid['key_count']][$subject_uuid['menu_key']] = "{$subject_uuid['option']} | Disabled no-link";
        break;

      default:
        break;
    }
  }

  /**
   * Checks for a menu item type of separator.
   *
   * @param array $allowed_separators
   *   An array of allowed separators.
   * @param mixed $menu_item
   *   The menu item in the iteration.
   *
   * @return string|null
   *   String self::SEPARATOR if matched, NULL otherwise.
   */
  protected function checkForSeparator(array $allowed_separators, $menu_item) {
    // If we hit on one of our allowed menu separators, include it.
    foreach ($allowed_separators as $separator) {
      $matcher = preg_match('/^' . $separator . '/', $menu_item->getTitle());
      if ($matcher > 0) {
        return self::SEPARATOR;
      }
    }
    return NULL;
  }

  /**
   * Check for a simple match for a pattern.
   *
   * @param string $alias
   *   The current alias.
   *
   * @return string|null
   *   A state of self::ENABLED, or NULL.
   */
  protected function checkForSimpleMatch($alias) {
    foreach ($this->menuRules['universal_parent_menu_items'] as $parent_rule) {
      if ((strpos($alias, $parent_rule) !== FALSE) && (strpos($parent_rule, '~') === FALSE)) {
        // Check for simple match.
        $parent_sanitized = str_replace('/', '\/', $parent_rule);
        $matches = preg_match('/' . $parent_sanitized . '/', $alias);
        if ($matches > 0) {
          return self::ENABLED;
        }
      }
    }
    return NULL;
  }

  /**
   * Check for a match for pattern of Disabled parent with no children allowed.
   *
   * @param string $alias
   *   The current alias.
   *
   * @return string|null
   *   A state of self::DISABLED, or NULL.
   */
  protected function checkForTypeDisabledParent($alias) {
    foreach ($this->menuRules['universal_parent_menu_items'] as $parent_rule) {
      if (strpos($parent_rule, '~') !== FALSE) {
        // The ~ tells us we just have a disabled parent.
        $parent_rule = $this->removeWildCard('~', $parent_rule);
        if (strpos($alias, $parent_rule) !== FALSE) {
          // Check to see if this is the parent item.
          $parent_sanitized = str_replace('/', '\/', $parent_rule);
          if (preg_match("/{$parent_sanitized}$/", $alias)) {
            return self::DISABLED;
          }
        }
      }
    }
    return NULL;
  }

  /**
   * Check for a match for a pattern of enabled allowed parent.
   *
   * @param string $alias
   *   The current alias.
   *
   * @return string|null
   *   A state of self::ENABLED, self::DISABLED, or NULL.
   */
  protected function checkForDisabledParentWithChildren(string $alias) {
    foreach ($this->menuRules['universal_parent_menu_items'] as $parent_rule) {
      if (strpos($parent_rule, '!') !== FALSE) {
        // The ! tells us we have a disabled parent with children enabled.
        $parent_rule = $this->removeWildCard('!', $parent_rule);
        if (strpos($alias, $parent_rule) !== FALSE) {
          // It loosely matches the pattern.
          // Check to see if this is the parent item.
          $parent_sanitized = str_replace('/', '\/', $parent_rule);
          if (preg_match("/{$parent_sanitized}$/", $alias)) {
            return self::DISABLED;
          }
          // It's a child, so enable.
          return self::ENABLED;
        }
      }
    }
    return NULL;
  }

  /**
   * Used to filter disable all menu options when user shouldn't access.
   *
   * @param array $form
   *   The form array.
   * @param string $form_id
   *   The form id.
   */
  protected function disableMenuOptions(array &$form, string $form_id) {
    $this->setContentTypeInSettings($form, $form_id);

    if (!empty($form['menu']['link']['menu_parent']['#options'])) {
      $form['menu']['link']['title']['#attributes']['disabled'] = TRUE;
      $form['menu']['link']['description']['#attributes']['disabled'] = TRUE;
      $form['menu']['link']['link_enabled']['#attributes']['disabled'] = TRUE;
      $form['menu']['link']['weight']['#attributes']['disabled'] = TRUE;
    }
  }

  /**
   * Gets the menu type based on the alias.
   *
   * @param string $alias
   *   The alias to check.
   *
   * @return string|null
   *   The type of menu entry this would be based on path.
   */
  protected function getMenuItemType($alias) {
    $type = NULL;
    if ($alias) {
      $type = $this->checkForDisabledParentWithChildren($alias)
      ?? $this->checkForTypeDisabledParent($alias)
      ?? $this->checkForSimpleMatch($alias);
    }
    return $type;
  }

  /**
   * Builds menu access settings and populates $config.
   */
  protected function buildMenuAccessRules() {
    // Grab the allowed parents config.
    $allowed_parents = $this->settings->get('va_gov_menu_access.paths');
    $allowed_parents_array = explode("\n", $allowed_parents);
    // Push items into parent array.
    foreach ($allowed_parents_array as $allowed_parent) {
      if (strpos($allowed_parent, '%') !== FALSE) {
        $this->menuRules['universal_parent_menu_items'][] = $this->removeWildCard('%', $allowed_parent);
      }
    }
    // Grab the locked_paths config.
    $locked_paths = $this->settings->get('va_gov_menu_access.locked_paths');
    $locked_paths_array = explode("\n", $locked_paths);
    // Sort locked paths into universal and single allowance arrays.
    foreach ($locked_paths_array as $locked_path) {
      if (strpos($locked_path, '%') !== FALSE) {
        $this->menuRules['universal_locked_paths'][] = $this->removeWildCard('%', $locked_path);
      }
      else {
        $this->menuRules['single_locked_paths'][] = trim($locked_path);
      }
    }
  }

  /**
   * Removes the wildcard character from the string.
   *
   * @param string $wildcard
   *   The wildcard string to remove.
   * @param string $path
   *   The path to remove the wildcard from.
   *
   * @return string
   *   The path with the wildcard removed.
   */
  protected function removeWildCard($wildcard, $path) {
    $path = str_replace($wildcard, '', $path);
    $path = trim($path);
    return $path;
  }

  /**
   * Extract menu ids and other date from parent options.
   *
   * @param mixed $parent_options
   *   Array of options from the menu link.
   *
   * @return array
   *   An array of menu ids and related metadata.
   */
  protected function extractMenuIds($parent_options) : array {
    $uuids_with_vals = [];
    $key_count = 0;
    foreach ($parent_options as $key => $option) {
      if (!empty(explode('menu_link_content:', $key))) {
        // This is how we get our menu item uuid.
        $menu_id_array = explode('menu_link_content:', $key);
        if (!empty($menu_id_array[1])) {
          $menu_id = $menu_id_array[1];
          $uuids_with_vals[$menu_id] = [
            'key_count' => $key_count++,
            'menu_key' => $key,
            'option' => $option,
          ];
        }
      }
    }
    return $uuids_with_vals;
  }

  /**
   * Used to close out a session of the menu reduction service so no persist.
   */
  protected function nuke() {
    unset($this->formState);
    $this->formState = NULL;
    unset($this->nodeBundle);
    $this->nodeBundle = NULL;
    unset($this->currentMenuParent);
    $this->currentMenuParent = '';
    unset($this->originalMenuParentOptions);
    $this->originalMenuParentOptions = [];
  }

  /**
   * Checks to see if the menu parent would be changed, backs out if has.
   *
   * @param array $form
   *   The form array by reference.
   */
  protected function preventUnintendedChangeOfParent(array &$form) {
    $current_menu_item_is_present = isset($form['menu']['link']['menu_parent']['#options'][$this->currentMenuParent]);
    if ($this->formState->getFormObject()->getEntity()->isNew()) {
      // Reapply the value that was set when submitted.
      $form['menu']['link']['menu_parent']['#value'] = $this->currentMenuParent;
    }
    // Check for rare possibility that menu parent is the default menu root.
    elseif ($this->currentMenuParent === "pittsburgh-health-care:") {
      return;
    }
    // This is not new so check the current parent exists in the reduced form.
    elseif (!$current_menu_item_is_present || $this->isCurrentMenuSettingDisabled($form)) {
      // Parent does not exist in reduced form, Put the original parent options
      // back to prevent data loss. The existing menu setting should not be
      // allowed, but it exists, so allow it to persist. It may have been
      // created and approved by a content_admin.
      $form['menu']['link']['menu_parent']['#options'] = $this->originalMenuParentOptions;
      // Lock it so the parent can not be changed.
      $form['menu']['link']['menu_parent']['#attributes']['disabled'] = TRUE;
      $title = $form['menu']['link']['menu_parent']['#title'];
      $can_not_change = $this->t('Can not be changed.');
      $title .= " ($can_not_change)";
      $form['menu']['link']['menu_parent']['#title'] = $title;
    }
  }

  /**
   * Checks to see if the current menu parent is disabled.
   *
   * @param array $form
   *   The form array.
   *
   * @return bool
   *   TRUE if the current menu parent is disabled.  FALSE otherwise.
   */
  protected function isCurrentMenuSettingDisabled(array $form) : bool {
    $is_disabled = FALSE;
    $current_menu_setting = $form['menu']['link']['menu_parent']['#options'][$this->currentMenuParent] ?? '';
    if (strpos($current_menu_setting, 'Disabled no-link') !== FALSE) {
      $is_disabled = TRUE;
    }

    return $is_disabled;
  }

  /**
   * Set the ['vagov_menu_access']['content_type'] on drupal settings.
   *
   * @param array $form
   *   The form array by reference.
   * @param string $form_id
   *   The form id.
   */
  protected function setContentTypeInSettings(array &$form, string $form_id) {
    // Case race. First to evaluate TRUE wins.
    switch (TRUE) {
      case (strpos($form_id, 'vet_center') === TRUE):
        $form_type_flag = 'vet-center';
        break;

      case (strpos($form_id, 'health_care_region_detail_page') === FALSE):
        $form_type_flag = 'not-allowed-to-operate-on-menu';
        break;

      default:
        $form_type_flag = 'detail-page';
        break;
    }

    $form['#attached']['drupalSettings']['vagov_menu_access'] = [
      'content_type' => $form_type_flag,
    ];
  }

}
