<?php

namespace Drupal\va_gov_menu_access\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements va_gov_menu_access_config_form menu access form.
 */
class VaGovMenuAccessConfigForm extends ConfigFormBase {
  /**
   * The cache manager service.
   *
   * @var Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->cacheManager = $container->get('cache.menu');

    return $instance;
  }

  /**
   * Grabs the menu form ID.
   */
  public function getFormId() {
    return 'va_gov_menu_access_config_form';
  }

  /**
   * Check if menu belongs to vamc.
   *
   * @param string $menu
   *   The menu item to test.
   *
   * @return string
   *   A vamc menu item.
   */
  private function getVamcMenu(string $menu) {
    if (substr($menu, 0, 3) === 'VA ') {
      return $menu;
    }
  }

  /**
   * The menu access config form..
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Remove the authenticated user role from the results.
    $site_user_roles = user_role_names(TRUE);
    $site_user_role_options = [];
    foreach ($site_user_roles as $site_user_role => $role) {
      $site_user_role_options[$site_user_role] = $role;
    }

    // Unset the authenticated, content_admin and administrator roles.
    unset($site_user_role_options['authenticated']);
    unset($site_user_role_options['content_admin']);
    unset($site_user_role_options['administrator']);
    $menus_all = menu_ui_get_menus();
    $vamc_menus = array_filter($menus_all, 'self::getVamcMenu');

    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    // Default settings.
    $config = $this->config('va_gov_menu_access.settings');

    // Form Render.
    $form['va_gov_menu_access_buildform'] = [
      '#type' => 'va_gov_menu_access_buildform',
    ];
    $form['va_gov_menu_access_roles'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Roles:'),
    ];
    $form['va_gov_menu_access_roles']['roles'] = [
      '#type' => 'checkboxes',
      '#options' => $site_user_role_options,
      '#title' => $this->t('Allow menu access to role(s):'),
      '#default_value' => $config->get('va_gov_menu_access.roles') ? $config->get('va_gov_menu_access.roles') : '',
      '#description' => $this->t('Please specify roles that should have menu access'),
    ];
    $form['va_gov_menu_access_menus'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Menus:'),
    ];
    $form['va_gov_menu_access_menus']['menus'] = [
      '#type' => 'checkboxes',
      '#options' => $vamc_menus,
      '#title' => $this->t('Allow menu access to:'),
      '#default_value' => $config->get('va_gov_menu_access.menus') ? $config->get('va_gov_menu_access.menus') : '',
      '#description' => $this->t('This will only apply to the roles selected above'),
    ];
    $form['va_gov_menu_access_paths'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Paths:'),
    ];
    $form['va_gov_menu_access_paths']['paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Restrict Path Access To:'),
      '#default_value' => $config->get('va_gov_menu_access.paths'),
      '#description' => $this->t('Put each path on its own line | These restrictions will override role selections made above'),
    ];

    return parent::buildForm($form, $form_state);

  }

  /**
   * Submit the menu form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('va_gov_menu_access.settings');

    // Roles.
    $selected_roles = [];
    foreach ($form_state->getValue('roles') as $role_key => $role_val) {
      if (!is_numeric($role_val)) {
        $selected_roles[] = $role_val;
      }
    }
    // Menus.
    $selected_menus = [];
    foreach ($form_state->getValue('menus') as $menu_key => $menu_val) {
      if (!is_numeric($menu_val)) {
        $selected_menus[$menu_key] = $menu_val;
      }
    }
    $config->set('va_gov_menu_access.roles', $selected_roles);
    $config->set('va_gov_menu_access.menus', $selected_menus);
    $config->set('va_gov_menu_access.paths', $form_state->getValue('paths'));

    $config->save();

    // Rebuild the menu cache.
    $this->cacheManager->invalidateAll();

    return parent::submitForm($form, $form_state);
  }

  /**
   * This allows the form to modify settings data.
   */
  protected function getEditableConfigNames() {
    return [
      'va_gov_menu_access.settings',
    ];
  }

}
