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
   * The menu access config form..
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    // Default settings.
    $config = $this->config('va_gov_menu_access.settings');

    // Form Render.
    $form['va_gov_menu_access_buildform'] = [
      '#type' => 'va_gov_menu_access_buildform',
    ];
    $form['va_gov_menu_access_paths'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('URL Paths'),
    ];
    $form['va_gov_menu_access_paths']['paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Allow children menu items on these URL paths'),
      '#default_value' => $config->get('va_gov_menu_access.paths'),
      '#description' => $this->t('Put each path on its own line | use "*" for wildcards'),
    ];

    $form['va_gov_menu_access_paths']['locked_paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Url paths that are locked from menu editing'),
      '#default_value' => $config->get('va_gov_menu_access.locked_paths'),
      '#description' => $this->t('Put each path on its own line | use "*" for wildcards'),
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

    $config->set('va_gov_menu_access.paths', $form_state->getValue('paths'));
    $config->set('va_gov_menu_access.locked_paths', $form_state->getValue('locked_paths'));

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
