<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

/**
 * Class for processing facility status to GovDelivery Bulletin.
 */
class BuildFrontend {

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  const WEB_ENVIRONMENTS = [
    'prod' => 'https://www.va.gov',
    'staging' => 'https://staging.va.gov',
    'dev' => 'https://dev.va.gov',
  ];

  /**
   * BuildFrontend constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger interface.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   */
  public function __construct(MessengerInterface $messenger, LoggerChannelFactoryInterface $logger_factory) {
    $this->messenger = $messenger;
    $this->logger = $logger_factory->get('va_gov_build_trigger');
  }

  /**
   * Get the WEB Url for a desired environment type.
   *
   * @param string $environment_type
   *   The environment type.
   *
   * @retrurn string
   *   The location of the frontend web for the environment.
   */
  public function getWebUrl($environment_type = NULL) {
    // Get the environment_type if not provided.
    $environment_type = (!empty($environment_type)) ? $environment_type : $this->getEnvironment();
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
   * Triggers the appropriate frontend Build based on the environment.
   */
  public function triggerFrontendBuild() {
    $jenkins_build_environment = Settings::get('jenkins_build_env');
    if (($this->getEnvironment() === 'ci') && (class_exists('DevShopTaskApiClient')) && (PHP_SAPI !== 'cli')) {
      // Running in CMS-CI and DevShopTaskApiClient has been loaded, use it.
      $task_json = \DevShopTaskApiClient::create('vabuild');
      $task = json_decode($task_json);
      if (!empty($task->nid)) {
        $vars = [
          '@link' => Link::fromTextAndUrl(t('Deploy Log'), Url::fromUri('http://' . $_SERVER['DEVSHOP_HOSTNAME'] . '/node/' . $task->nid))->toString(),
        ];
        $message = t('VA Web Rebuild & Deploy has been queued. The process should complete in around 1 minute. @link', $vars);
        $this->messenger->addStatus($message);
        $this->logger->info($message);

        // Save pending state.
        $this->setPendingState(1);
      }
      else {
        // This has failed due to bad devshop setting.
        $message = t('VA Web Rebuild & Deploy has NOT been queued because @method returned no id.', ['@method' => "\DevShopTaskApiClient::create('vabuild')"]);
        $this->setPendingState(0);
        $this->messenger->addError($message);
        $this->logger->error($message);
      }
    }
    elseif ($this->getEnvironment() === 'lando') {
      // This is a local dev environment.
      $vars = ['@command' => 'lando composer va:web:build'];
      $message = t('Frontend build would have been triggered. To build with Lando, run the command: @command', $vars);
      $this->messenger->addStatus($message);
      $this->logger->info($message);
      // Save pending state.
      $this->setPendingState(1);
    }
    elseif ((!empty($jenkins_build_environment)) && array_key_exists($jenkins_build_environment, self::WEB_ENVIRONMENTS)) {
      // This is in a BRD environment.
      $va_cms_bot_github_username = Settings::get('va_cms_bot_github_username');
      $va_cms_bot_github_auth_token = Settings::get('va_cms_bot_github_auth_token');
      $jenkins_build_job_host = Settings::get('jenkins_build_job_host');
      $jenkins_build_job_path = Settings::get('jenkins_build_job_path');
      $jenkins_build_job_url = Settings::get('jenkins_build_job_url');

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
          $this->logger->warning('Retry site build - attempt #' . $retry);
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
          $vars = [
            ':status_code' => $response->getStatusCode(),
            ':reason_phrase' => $response->getReasonPhrase(),
            ':url' => $jenkins_build_job_url,
          ];
          $message = t('Site rebuild failed with status code {:status_code} {:reason_phrase} and URL {:url}.', $vars);
          $this->messenger->addError($message);
          $this->logger->error($message);

          $message = t('Site rebuild request has failed for :url, check log for more information.', [':url' => $jenkins_build_job_url]);
          $this->messenger->addError($message);
          $this->logger->error($message);
        }
        else {
          $this->recordBuildTime();
          $vars = [
            ':url' => $jenkins_build_job_url,
            '@job_link' => $jenkins_build_job_host . $jenkins_build_job_path,
          ];
          $message = t('Site rebuild request has been triggered with :url. Please visit <a href="@job_link">@job_link</a> to see status.', $vars);
          $this->messenger->addStatus($message);
          $this->logger->info($message);
        }
      }
      catch (RequestException $exception) {
        $message = t('Site rebuild request has failed for :url with an Exception, check log for more information. If this is the PROD environment please notify in #cms-engineering Slack and please email vacmssupport@va.gov immediately with the error message you see here.', [':url' => $jenkins_build_job_url]);
        $this->messenger->addError($message);
        $this->logger->error($message);
        $this->setPendingState(0);
        watchdog_exception('va_gov_build_trigger', $exception);
      }
    }
    else {
      // In an unaccounted for environment. Call it off.
      $message = t('You cannot trigger a build in this environment. Only the DEV, STAGING and PROD environments support triggering builds.');
      $this->messenger->addWarning($message);
      $this->logger->warning($message);
      $this->setPendingState(0);
      return FALSE;
    }
  }

  /**
   * Records the build time of the request.
   */
  private function recordBuildTime() {
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
  }

  /**
   * Set the config state of build pending.
   *
   * @param bool $state
   *   The state that should be set for build pending.
   */
  public function setPendingState($state) {
    $state = (!empty($state)) ? 1 : 0;
    $config = \Drupal::service('config.factory')->getEditable('va_gov.build');
    $config->set('web.build.pending', $state);
    $config->save();
  }

  /**
   * Determines the environment type.
   *
   * @return string
   *   The name of the environment.
   */
  public function getEnvironment() {
    return getenv('CMS_ENVIRONMENT_TYPE') ?: 'ci';
  }

  /**
   * Check to see if this had a status or status info change.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object of a node just updated or saved.
   *
   * @return bool
   *   TRUE if there was a status related change, FALSE if there was not.
   */
  private function changedStatus(NodeInterface $node) {
    // Check for change of workflow to published.
    $mod_state = $node->get('moderation_state')->value;
    $mod_state_original = $this->getOriginalFieldValue($node, 'moderation_state');
    if (($mod_state === 'published') && ($mod_state !== $mod_state_original)) {
      // The status is published and was not before.
      return TRUE;
    }

    // Check for change of operating status.
    $status_field = 'field_operating_status_facility';
    if ($node->hasField($status_field)) {
      $operating_status = $node->get($status_field)->value;
      $original_operating_status = $this->getOriginalFieldValue($node, $status_field);
      if ($operating_status !== $original_operating_status) {
        return TRUE;
      }
    }

    // Check for change of operating status more info.
    $status_info_field = 'field_operating_status_more_info';
    if ($node->hasField($status_info_field)) {
      $additional_info = $node->get($status_info_field)->value;
      $original_additional_info = $this->getOriginalFieldValue($node, $status_info_field);
      if ($additional_info !== $original_additional_info) {
        return TRUE;
      }
    }
    // Made it this far, nothing changed.
    return FALSE;
  }

  /**
   * Gets the previously saved value of a field.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object of a node just updated or saved.
   * @param string $fieldname
   *   The machine name of the field to get.
   *
   * @return string
   *   The value of the field, or '' if not found.
   */
  private function getOriginalFieldValue(NodeInterface $node, $fieldname) {
    $value = '';
    if (isset($node->original) && ($node->original instanceof NodeInterface)) {
      // There was a previous save.
      $value = $node->original->get($fieldname)->value;
    }

    return $value;
  }

  /**
   * Method to trigger a frontend build as the result of a save.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object of a node just updated or saved.
   */
  public function triggerFrontendBuildFromContentSave(NodeInterface $node) {
    $allowed_content_types = [
      'full_width_banner_alert',
      'health_care_local_facility',
    ];
    if (in_array($node->getType(), $allowed_content_types)) {
      // This is the right content type to trigger a build. Is it published?
      if ($node->isPublished()) {
        // It is published.
        if ($node->getType() === 'health_care_local_facility') {
          // This is a facility, check if the status or status info changed.
          if ($this->changedStatus($node)) {
            // The status changed so trigger a build.
            $this->triggerFrontendBuild();
          }
        }
        else {
          $this->triggerFrontendBuild();
        }
      }
    }
  }

}
