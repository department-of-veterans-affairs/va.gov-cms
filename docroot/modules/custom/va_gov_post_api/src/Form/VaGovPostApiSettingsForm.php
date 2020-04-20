<?php

namespace Drupal\va_gov_post_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class VaGovPostApiSettingsForm.
 */
class VaGovPostApiSettingsForm extends FormBase {

  /**
   * Class constructor.
   */
  public function __construct() {}

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'va_gov_post_api_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('va_gov_post_api.settings');

    $form['bypass_data_check'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Bypass data comparison'),
      '#description' => $this->t('If checked, all saved nodes will be queued for syncing bypassing a requirement for updated data.'),
      '#default_value' => $config->get('bypass_data_check'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#weight' => '20',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = self::configFactory()->getEditable('va_gov_post_api.settings');
    $config
      ->set('bypass_data_check', $form_state->getValue('bypass_data_check'))
      ->save();
  }

}
