<?php

namespace Drupal\va_gov_build_trigger\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Client;

/**
 * Implements build trigger form.
 */
class BuildTriggerForm extends FormBase {

  /**
   * Build the build trigger form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Rebuild site'),
      '#description' => 'yes',
      '#button_type' => 'primary',
    ];
    if (!in_array(getenv('ENVIRONMENT_TYPE'), [
      'dev',
      'stg',
      'prod',
    ])) {
      \Drupal::messenger()
        ->addMessage(t('You cannot trigger a build in this environment. Only the DEV, STAGING and PROD environments support triggering builds.'), 'warning');
      $form['actions']['submit']['#attributes'] = [
        'disabled' => 'disabled',
      ];
    }
    return $form;
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller.  it must
   * be unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'va_gov_build_trigger_build_trigger_form';
  }

  /**
   * Submit the build trigger form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $va_socks_proxy_url = Settings::get('va_socks_proxy_url');
    $va_cms_bot_github_username = Settings::get('va_cms_bot_github_username');
    $va_cms_bot_github_auth_token = Settings::get('va_cms_bot_github_auth_token');
    $va_jenkins_build_host = Settings::get('va_jenkins_build_host');
    $va_jenkins_build_job_dev_staging = Settings::get('va_jenkins_build_job_dev_staging');
    $va_jenkins_build_job_prod = Settings::get('va_jenkins_build_job_prod');
    $va_jenkins_build_job_url_params = Settings::get('va_jenkins_build_job_url_params');
    if (!$va_jenkins_build_job_url_params) {
      \Drupal::messenger()
        ->addMessage(t('You cannot trigger a build in this environment. Only the DEV, STAGING and PROD environments support triggering builds.'), 'warning');
      return FALSE;
    }
    $va_jenkins_build_url = $va_jenkins_build_host . $va_jenkins_build_job_url_params;

    try {
      // Setup the REQUEST options.
      $request_options = [
        'verify' => TRUE,
        'body' => '',
        'headers' => [
          'Accept' => 'text/plain',
        ],
        'curl' => [
          CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
          CURLOPT_VERBOSE => TRUE,
          CURLOPT_URL => $va_jenkins_build_url,
          CURLOPT_POST => 1,
          CURLOPT_PROXY => $va_socks_proxy_url,
          CURLOPT_USERNAME => $va_cms_bot_github_username,
          CURLOPT_PASSWORD => $va_cms_bot_github_auth_token,
          CURLOPT_FOLLOWLOCATION => 1,
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_HEADER => 1,
        ],
      ];
      // Setup the REQUEST retry logic w/lazy backoff.
      // @link http://dawehner.github.io/php,/guzzle/2017/05/19/guzzle-retry.html
      $handler_stack = HandlerStack::create();
      $handler_stack->push(Middleware::retry(function ($retry, $request, $response, $reason) {
        // Must be a "201 Created" response code & message, if not then continue
        // and retry.
        if ($response && $response->getStatusCode() === 201) {
          return FALSE;
        }
        FormBase::logger('va_gov_build_trigger')
          ->warning('Retry site build - attempt #' . $retry);
        // Stop after 3 retries.
        return $retry < 3;
      }, function ($retry) {
        $delay_ms = 1000;
        return $retry * $delay_ms;
      }));

      // Handle the RESPONSE.
      $client = new Client(['handler' => $handler_stack]);
      $response = $client->post($va_socks_proxy_url, $request_options);

      if ($response->getStatusCode() !== 201) {
        FormBase::logger('va_gov_build_trigger')
          ->error('Site rebuild failed with status code {:status_code} {:reason_phrase} and URL {:url}.',
              [
                ':status_code' => $response->getStatusCode(),
                ':reason_phrase' => $response->getReasonPhrase(),
                ':url' => $va_jenkins_build_url,
              ]
          );
        \Drupal::messenger()
          ->addMessage(t('Site rebuild request has failed for :url, check log for more information.', [':url' => $va_jenkins_build_url]), 'error');
      }
      else {
        // Get our SQL formatted date.
        $time_raw = \Drupal::service('date.formatter')
          ->format(time(), 'html_datetime', '', 'UTC');
        $time = strtok($time_raw, '+');

        // We only need to update field table - field is set on node import.
        $query = \Drupal::database()
          ->update('node__field_page_last_built')
          ->fields(['field_page_last_built_value' => $time]);
        $query->execute();

        // We only need to update - revision field is set on node import.
        $query_revision = \Drupal::database()
          ->update('node_revision__field_page_last_built')
          ->fields(['field_page_last_built_value' => $time]);
        $query_revision->execute();

        if (in_array(Settings::get('va_jenkins_build_env'), [
          'dev',
          'staging',
        ])) {
          \Drupal::messenger()
            ->addMessage(t('Site rebuild request has been triggered for :url. Please visit <a href="@job_link">@job_link</a> to see status.', [
              ':url' => $va_jenkins_build_url,
              '@job_link' => $va_jenkins_build_host . $va_jenkins_build_job_dev_staging,
            ]), 'status');
        }
        elseif (in_array(Settings::get('va_jenkins_build_env'), [
          'prod',
        ])) {
          \Drupal::messenger()
            ->addMessage(t('Site rebuild request has been triggered for :url. Please visit <a href="@job_link">@job_link</a> to see status.', [
              ':url' => $va_jenkins_build_url,
              '@job_link' => $va_jenkins_build_host . $va_jenkins_build_job_prod,
            ]), 'status');
        }
      }
    }
    catch (RequestException $exception) {
      \Drupal::messenger()
        ->addMessage(t('Site rebuild request has failed for :url with an Exception, check log for more information. If this is the PROD environment please notify cms-admin@va.gov immediately.', [':url' => $va_jenkins_build_url]), 'error');
      watchdog_exception('va_gov_build_trigger', $exception);

    }
  }

}
