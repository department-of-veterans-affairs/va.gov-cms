<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\slack\Slack;
use Drupal\va_gov_build_trigger\Environment\EnvironmentPluginBase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * BRD Plugin for Environment.
 *
 * @Environment(
 *   id = "brd",
 *   label = @Translation("BRD")
 * )
 */
class BRD extends EnvironmentPluginBase implements ContainerFactoryPluginInterface {

  // Hosts we associate with the Prod BRD environment.
  public const VAGOV_PRODUCTION_HOSTS = [
    'cms.va.gov',
    'prod.cms.va.gov',
  ];

  use StringTranslationTrait;

  /**
   * Date Formatter Service..
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Slack service.
   *
   * @var \Drupal\slack\Slack
   */
  protected $slack;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    $dateFormatter,
    $database,
    RequestStack $requestStack,
    Slack $slack
  ) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dateFormatter = $dateFormatter;
    $this->database = $database;
    $this->request = $requestStack->getCurrentRequest();
    $this->slack = $slack;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('date.formatter'),
      $container->get('database'),
      $container->get('request_stack'),
      $container->get('slack.slack_service')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function triggerFrontendBuild(): void {
    $va_cms_bot_github_username = Settings::get('va_cms_bot_github_username');
    $va_cms_bot_jenkins_auth_token = Settings::get('va_cms_bot_jenkins_auth_token');
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
          CURLOPT_PASSWORD => $va_cms_bot_jenkins_auth_token,
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
        $message = $this->t('Site rebuild failed with status code {:status_code} {:reason_phrase} and URL {:url}.', $vars);
        $this->messenger()->addError($message);
        $this->logger->error($message);

        $message = $this->t('Site rebuild request has failed for :url, check log for more information.', [':url' => $jenkins_build_job_url]);
        $this->messenger()->addError($message);
        $this->logger->error($message);
      }
      else {
        $this->recordBuildTime();
        $vars = [
          ':url' => $jenkins_build_job_url,
          '@job_link' => $jenkins_build_job_host . $jenkins_build_job_path,
        ];
        $message = $this->t('Site rebuild request has been triggered with :url. Please visit <a href="@job_link">@job_link</a> to see status.', $vars);
        $this->messenger()->addStatus($message);
        $this->logger->info($message);
      }
    }
    catch (RequestException $exception) {
      $message = $this->t('Site rebuild request has failed for :url with an Exception, check log for more information.', [':url' => $jenkins_build_job_url]);
      $this->messenger()->addError($message);
      $this->logger->error($message);
      $this->webBuildStatus->disableWebBuildStatus();
      watchdog_exception('va_gov_build_trigger', $exception);
      if (in_array($this->request->getHost(), static::VAGOV_PRODUCTION_HOSTS)) {
        $slackConfig = $this->config->get('slack.settings');
        if ($slackConfig->get('slack_webhook_url')) {
          $this->slack->sendMessage("@here :warning: $message");
        }
      }
    }

  }

  /**
   * Records the build time of the request.
   */
  private function recordBuildTime() {
    // Get our SQL formatted date.
    $time_raw = $this->dateFormatter
      ->format(time(), 'html_datetime', '', 'UTC');
    $time = strtok($time_raw, '+');

    // We only need to update field table - field is set on node import.
    $query = $this->database
      ->update('node__field_page_last_built')
      ->fields(['field_page_last_built_value' => $time]);
    $query->execute();

    // We only need to update - revision field is set on node import.
    $query_revision = $this->database
      ->update('node_revision__field_page_last_built')
      ->fields(['field_page_last_built_value' => $time]);
    $query_revision->execute();
  }

  /**
   * {@inheritDoc}
   */
  public function shouldTriggerFrontendBuild(): bool {
    return TRUE;
  }

}
