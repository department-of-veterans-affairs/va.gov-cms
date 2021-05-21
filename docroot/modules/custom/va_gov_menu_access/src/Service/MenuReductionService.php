<?php

namespace Drupal\va_gov_menu_access\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeForm;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\va_gov_user\Service\UserPermsService;

/**
 * Class MenuReductionService a service for reducing possible menu items.
 */
class MenuReductionService {

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
    $this->originalMenuParentOptions = $form['menu']['link']['menu_parent']['#options'];
    $this->setEmptyMenuParentSelector($form);

    // @todo Remove this nonsense check to activate the remainder of this logic
    // when the other issues in #2427 have paved the way for adjusting and
    // testing this logic.
    if (TRUE) {
      $this->nuke();
      return;
    }
    // End of @todo removal.  Nothing below this point will run.
    if ($this->userPermsService->hasAdminRole()) {
      // User is an admin so no menu reduction needed.
      $this->nuke();
      return;
    }

    $form_id = $this->formState->getFormObject()->getFormId();
    // RISK: This is a risky pattern tying menu governance to page alias.
    // Only config joins menu and alias, a config change could break this.
    // This risk can be mitigated with a different approach once the majority
    // of the tasks in Issue #2427 have been addressed.
    $page_alias = !empty($form['path']['widget'][0]['alias']['#default_value']) ? $form['path']['widget'][0]['alias']['#default_value'] : '';

    // Health care region detail pages are special because they have no
    // pattern based alias and need their own handling.
    if (($this->nodeBundle !== 'health_care_region_detail_page') || ($this->isLockedPath($page_alias))) {
      // We have a match, lock it down.
      $this->disableMenuOptions($form, $form_id);
    }

    $this->applyVamcMenuRulesForDetailPage($form);

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
    $match = FALSE;
    if (empty($page_alias)) {
      // There is no alias to match.
      return $match;
    }
    // In this loop, we look to see if we have either an all children
    // pattern lock, or a specific parent pattern lock.
    foreach ($this->menuRules['universal_locked_paths'] as $path) {
      if (strpos($path, '*') !== FALSE) {
        $path_sanitized = rtrim($path, '*');
        if (strpos($page_alias, $path_sanitized) !== FALSE) {
          $match = TRUE;
          break;
        }
      }
      else {
        if (strpos($page_alias, $path) !== FALSE) {
          $path_sanitized = str_replace('/', '\/', $path);
          $matches = preg_match('/' . $path_sanitized . '$/', $page_alias);
          if ($matches > 0) {
            $match = TRUE;
            break;
          }
        }
      }
    }

    if (!$match && in_array($page_alias, $this->menuRules['single_locked_paths'])) {
      // The page is a match for the single locked paths.
      $match = TRUE;
    }

    return $match;
  }

  /**
   * Set the menu parent selector to the default empty if empty.
   *
   * @param array $form
   *   The form array by reference.
   */
  protected function setEmptyMenuParentSelector(array &$form) {
    if (!empty($form['menu'])) {
      // Sets an empty Select prompt on parent menu selector for all node forms.
      $form['menu']['link']['menu_parent']['#options'] = array_merge(['0' => '- Select a value -'], $form['menu']['link']['menu_parent']['#options']);
      // If we don't have a value set, yet, ensure the Select appears first.
      if (!$this->isMenuEnabled()) {
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

    $uuids_with_vals = $this->extractMenuIds($form['menu']['link']['menu_parent']['#options']);

    // Load up all our menu items at once.
    $loaded_menu_items = $this->entityTypeManager
      ->getStorage('menu_link_content')
      ->loadByProperties([
        'uuid' => array_keys($uuids_with_vals),
        'enabled' => 1,
      ]);

    // Loop through them, and decide whether it should be allowed in options.
    foreach ($loaded_menu_items as $key => $menu_item) {
      $uri = $menu_item->get('link')->uri;

      if (substr($uri, 0, 12) === 'entity:node/' || $uri === 'route:<nolink>') {
        // Get our alias, as well as last arg of alias for comparison.
        $uri_parts = explode('entity:node/', $uri);
        $nid = end($uri_parts);
        $alias = $this->aliasManager->getAliasByPath('/node/' . $nid);

        $subject_uuid = $uuids_with_vals[$menu_item->get('uuid')->value];

        // In this loop, we look to see if we have either an all children
        // pattern lock, or a specific parent pattern lock match.
        $menu_element_type = $this->getMenuType($alias);

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
      if (strpos($alias, $parent_rule) !== FALSE) {
        // Check for simple match.
        $parent_sanitized = str_replace('/', '\/', $parent_rule);
        $matcher = preg_match('/' . $parent_sanitized . '$/', $alias);
        if ($matcher > 0) {
          return self::ENABLED;
        }
      }
    }
    return NULL;
  }

  /**
   * Check for a match for a pattern of Disabled parent.
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
        $parent_sanitized = rtrim($parent_rule, '~');
        if (strpos($alias, $parent_sanitized) !== FALSE) {
          // Check to see if this is the parent item.
          $parent_sanitized = str_replace('/', '\/', $parent_sanitized);
          if (preg_match('/' . $parent_sanitized . '$/', $alias)) {
            return self::DISABLED;
          }
        }
      }
    }
    return NULL;
  }

  /**
   * Check for a match for a pattern of Disabled parent with enabled children.
   *
   * @param string $alias
   *   The current alias.
   *
   * @return string|null
   *   A state of self::ENABLED, self::DISABLED, or NULL.
   */
  protected function checkForTypeParentWithEnabledChildren(string $alias) {
    foreach ($this->menuRules['universal_parent_menu_items'] as $parent_rule) {
      if (strpos($parent_rule, '!') !== FALSE) {
        // The ! tells us we have a disabled parent with children enabled.
        $parent_sanitized = rtrim($parent_rule, '!');
        if (strpos($alias, $parent_sanitized) !== FALSE) {
          // Check to see if this is the parent item.
          $parent_sanitized = str_replace('/', '\/', $parent_sanitized);
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
   * Gets the meny type based on the alias.
   *
   * @param string $alias
   *   The alias to check.
   *
   * @return string|null
   *   The type of menu entry this would be based on path.
   */
  protected function getMenuType($alias) {
    $type = NULL;
    if ($alias) {
      $type = $this->checkForTypeParentWithEnabledChildren($alias)
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
    foreach ($allowed_parents_array as $parent) {
      if (strpos($parent, '%') !== FALSE) {
        $universal_parent_menu_items_raw = explode('%', $parent);
        $this->menuRules['universal_parent_menu_items'][] = trim(end($universal_parent_menu_items_raw));
      }
    }
    // Grab the locked_paths config.
    $locked_paths = $this->settings->get('va_gov_menu_access.locked_paths');
    $locked_paths_array = explode("\n", $locked_paths);
    // Sort locked paths into universal and single allowance arrays.
    foreach ($locked_paths_array as $locked_path) {
      if (strpos($locked_path, '%') !== FALSE) {
        $universal_locked_raw = explode('%', $locked_path);
        $this->menuRules['universal_locked_paths'][] = trim(end($universal_locked_raw));
      }
      else {
        $this->menuRules['single_locked_paths'][] = trim($parent);
      }
    }
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
    // Check to see if the current parent exists in the reduced form.
    if (!in_array($this->currentMenuParent, $form['menu']['link']['menu_parent']['#options'])) {
      // Parent does not exist in reduced form, Put the original parent options
      // back to prevent data loss. The existing menu setting should not be
      // allowed, but it exists, so allow it to persist. It may have been
      // created and approved by a content_admin.
      $form['menu']['link']['menu_parent']['#options'] = $this->originalMenuParentOptions;
      // The menu form itself should be hidden for non-admins.
      $form['menu']['#attributes'] = ['style' => 'display:none'];
    }
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
