<?php

namespace Drupal\va_gov_post_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;

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

    // Variables to compose Post API status.
    $set = '<span style="color:green">&#x2714;</span>';
    $error = '<span style="color:red">&#x2716;</span>';
    $slack_webhook = Settings::get('slack_webhook_url', NULL);
    $slack_webhook_indicator = $slack_webhook ? $set : $error;
    $slack_webhook_status_markup = $slack_webhook ? $slack_webhook : $this->t('Slack Webhook URL is not set in settings.php.');
    $post_api_status_link_markup = $this->t('<a href="@post_api_status">Post API Status</a>', ['@post_api_status' => Url::fromUri('internal:/admin/config/post-api/config')->toString()]);

    $form['status'] = [
      '#type' => 'details',
      '#title' => $this->t('VA.gov Post API status'),
      '#open' => TRUE,
    ];

    $form['status']['va_gov_post_api_status'] = [
      '#type' => 'markup',
      '#markup' => Markup::create('<p>' . $slack_webhook_indicator . ' <strong>' . $this->t('Slack Webhook URL:') . '</strong> ' . $slack_webhook_status_markup . '</p><p>' . $post_api_status_link_markup . '</p>'),
    ];

    $form['config'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuration options'),
      '#open' => TRUE,
    ];

    $form['config']['bypass_data_check'] = [
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
