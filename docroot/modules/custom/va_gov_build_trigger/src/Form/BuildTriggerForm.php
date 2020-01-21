<?php

namespace Drupal\va_gov_build_trigger\Form;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Client;

/**
 * Implements build trigger form.
 *
 * The environment variable CMS_ENVIRONMENT_TYPE is used to determine the URL
 * displayed.
 *
 * You can edit the .env file to change CMS_ENVIRONMENT_TYPE=prod to see what
 * the site will look like in production.
 */
class BuildTriggerForm extends FormBase {

  const WEB_ENVIRONMENTS = [
    'prod' => 'https://www.va.gov',
    'staging' => 'https://staging.va.gov',
    'dev' => 'https://dev.va.gov',
  ];

  /**
   * Get the WEB Url for a desired environment type.
   */
  public function getWebUrl($environment_type) {

    $cms_url = !empty(self::WEB_ENVIRONMENTS[$environment_type]) ?
      self::WEB_ENVIRONMENTS[$environment_type] :
      getenv('HTTP_HOST');

    // If this is not a Prod environment, link to /static site.
    if (empty(self::WEB_ENVIRONMENTS[$environment_type])) {
      $cms_url .= "/static";
    }

    return $cms_url;
  }

  /**
   * Build the build trigger form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $environment_type = getenv('CMS_ENVIRONMENT_TYPE')?: 'ci';
    $target = $this->getWebURL($environment_type);

    $form['actions']['#type'] = 'actions';
    $form['help_1'] = [
      '#prefix' => '<p>',
      '#markup' => t('This is a decoupled Drupal website. Content will not be visible in the front-end website until you run a "rebuild" and deploy it to an environment.'),
      '#suffix' => '</p>',
      '#weight' => -10,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Rebuild & Deploy Content'),
      '#button_type' => 'primary',
      '#suffix' => ' ' . t('to %site', [
        '%site' => $target,
      ]),
    ];

    $form['environment_type'] = [
      '#type' => 'value',
      '#value' => $environment_type,
    ];

    $form['environment'] = [
      '#type' => 'value',
      '#value' => $target,
    ];

    // Save pending state.
    $config = \Drupal::service('config.factory')->getEditable('va_gov.build');
    if ($config->get('web.build.pending', 0)) {
      $form['tip']['#prefix'] = '<em>';
      $form['tip']['#markup'] = t('A site rebuild is queued.');
      $form['tip']['#suffix'] = '</em>';
      $form['tip']['#weight'] = 100;
    }

    // Case race, first to evaluate TRUE wins.
    switch (TRUE) {
      case $environment_type == 'prod':
      case $environment_type == 'staging':
      case $environment_type == 'dev':
        $description = t('Rebuilds for this environment will be handled by VFS Jenkins.');
        break;

      case $environment_type == 'ci':
        $description = t('Rebuilds for this environment are handled by CMS-CI. You may press this button to trigger a full site rebuild. It will take around 45 seconds.');
        break;

      case $environment_type == 'lando':
        $description = t('Rebuilds for Lando sites must be run manually. Run the following command to regenerate the static site: <pre>lando composer va:web:build</pre>  The button below is used in CMS and production environments. You can use it to emulate their behavior. You may change the CMS_ENVIRONMENT_TYPE environment behavior to develop.');
        break;

      default:
        $description = t('Environment not detected. Rebuild by running the <pre>composer va:web:build</pre> command.');
    }

    $form['environment_target'] = [
      '#type' => 'item',
      '#title' => t('Environment Target'),
      '#markup' => Drupal::l($target, Url::fromUri('http://' . $target), [
        'attributes' => [
          'target' => '_blank',
        ],
      ]),
      '#description' => $description,
    ];
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
    // If running in CMS-CI and DevShopTaskApiClient has been loaded, use it.
    if ($form_state->getValue('environment_type') == 'ci' && class_exists('DevShopTaskApiClient')) {
      $task_json = \DevShopTaskApiClient::create('vabuild');
      $task = json_decode($task_json);
      if (!empty($task->nid)) {
        drupal_set_message(t('VA Web Rebuild & Deploy has been queued. The process should complete in around 1 minute.') . ' ' . Link::fromTextAndUrl(t('Deploy Log'), Url::fromUri('http://' . $_SERVER['DEVSHOP_HOSTNAME'] . '/node/' . $task->nid))->toString());

        // Save pending state.
        $config = \Drupal::service('config.factory')->getEditable('va_gov.build');
        $config->set('web.build.pending', 1);
        $config->save();

      }
    }
    elseif ($form_state->getValue('environment_type') == 'lando') {

      // Save pending state.
      $config = \Drupal::service('config.factory')->getEditable('va_gov.build');
      $config->set('web.build.pending', 1);
      $config->save();

    }
    else {

      $va_cms_bot_github_username = Settings::get('va_cms_bot_github_username');
      $va_cms_bot_github_auth_token = Settings::get('va_cms_bot_github_auth_token');
      $jenkins_build_job_host = Settings::get('jenkins_build_job_host');
      $jenkins_build_job_path = Settings::get('jenkins_build_job_path');
      $jenkins_build_job_url = Settings::get('jenkins_build_job_url');

      if (!in_array(Settings::get('jenkins_build_env'), [
        'dev',
        'staging',
        'prod',
      ])) {
        Drupal::messenger()
          ->addMessage(t('You cannot trigger a build in this environment. Only the DEV, STAGING and PROD environments support triggering builds.'), 'warning');
        return FALSE;
      }

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
            CURLOPT_URL => $jenkins_build_job_url,
            CURLOPT_POST => 1,
            // Authorize to the Jenkins API via GitHub login.
            CURLOPT_USERNAME => $va_cms_bot_github_username,
            CURLOPT_PASSWORD => $va_cms_bot_github_auth_token,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 1,
          ],
        ];
        // Setup the REQUEST retry logic w/lazy backoff.
        // @see http://dawehner.github.io/php,/guzzle/2017/05/19/guzzle-retry.html
        $handler_stack = HandlerStack::create();
        $handler_stack->push(Middleware::retry(function ($retry, $request, $response, $reason) {
          // Must be a "201 Created" response code & message, if not then cont
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
        $response = $client->post($jenkins_build_job_host, $request_options);

        if ($response->getStatusCode() !== 201) {
          FormBase::logger('va_gov_build_trigger')
            ->error('Site rebuild failed with status code {:status_code} {:reason_phrase} and URL {:url}.',
              [
                ':status_code' => $response->getStatusCode(),
                ':reason_phrase' => $response->getReasonPhrase(),
                ':url' => $jenkins_build_job_url,
              ]
            );
          Drupal::messenger()
            ->addMessage(t('Site rebuild request has failed for :url, check log for more information.', [':url' => $jenkins_build_job_url]), 'error');
        }
        else {
          // Get our SQL formatted date.
          $time_raw = Drupal::service('date.formatter')
            ->format(time(), 'html_datetime', '', 'UTC');
          $time = strtok($time_raw, '+');

          // We only need to update field table - field is set on node import.
          $query = Drupal::database()
            ->update('node__field_page_last_built')
            ->fields(['field_page_last_built_value' => $time]);
          $query->execute();

          // We only need to update - revision field is set on node import.
          $query_revision = Drupal::database()
            ->update('node_revision__field_page_last_built')
            ->fields(['field_page_last_built_value' => $time]);
          $query_revision->execute();

          Drupal::messenger()
            ->addMessage(t('Site rebuild request has been triggered with :url. Please visit <a href="@job_link">@job_link</a> to see status.', [
              ':url' => $jenkins_build_job_url,
              '@job_link' => $jenkins_build_job_host . $jenkins_build_job_path,
            ]), 'status');
        }
      }
      catch (RequestException $exception) {
        Drupal::messenger()
          ->addMessage(t('Site rebuild request has failed for :url with an Exception, check log for more information. If this is the PROD environment please notify in #cms-engineering Slack and please email vacmssupport@va.gov immediately with the error message you see here.', [':url' => $jenkins_build_job_url]), 'error');
        watchdog_exception('va_gov_build_trigger', $exception);

      }
    }
  }

}
