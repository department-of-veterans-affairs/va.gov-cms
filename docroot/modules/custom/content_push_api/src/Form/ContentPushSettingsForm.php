<?php

namespace Drupal\content_push_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ContentPushSettingsForm.
 */
class ContentPushSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_push_api_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('content_push_api.settings');

    $form['endpoint_host'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Endpoint'),
      '#description' => $this->t(
        'Stores CMS Facility API endpoint host in database - THIS IS NOT RECOMMENDED.
        Preferred method is to store in settings.local.php - see README for
        instructions.'),
      '#default_value' => $config->get('endpoint_host'),
    ];

    $form['apikey'] = [
      '#type' => 'password',
      '#title' => $this->t('API Key'),
      '#description' => $this->t(
        'Stores API key in database - THIS IS NOT RECOMMENDED.
        Preferred method is to store in settings.local.php - see README for
        instructions.'),
      '#default_value' => $config->get('apikey'),
    ];

    $form['header_content_type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header: Content-type'),
      '#description' => $this->t(
        'Stores Content-type header to use in HTTP request. E.g.
        "application/json"'),
      '#default_value' => $config->get('header_content_type'),
    ];

    $form['logging'] = [
      '#type' => 'details',
      '#title' => $this->t('Logging and Notifications'),
    ];

    $form['logging']['slack'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Slack Notifications'),
      '#description' => $this->t(
        'Enables calls to Slack webhook configured via environment variables (see README.md). If disabled, will still write to dblog.'
      ),
      '#default_value' => $config->get('slack'),
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
    $config = self::configFactory()->getEditable('content_push_api.settings');
    $config
      ->set('endpoint_host', $form_state->getValue('endpoint_host'))
      ->set('apikey', $form_state->getValue('apikey'))
      ->set('header_content_type', $form_state->getValue('header_content_type'))
      ->set('slack', $form_state->getValue('slack'))
      ->save();
  }

}
