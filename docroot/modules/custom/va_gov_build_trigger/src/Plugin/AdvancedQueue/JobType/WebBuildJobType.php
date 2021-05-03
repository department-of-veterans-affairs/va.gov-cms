<?php

namespace Drupal\va_gov_build_trigger\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\va_gov_build_trigger\Command\CommandRunner;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;
use Github\Api\Markdown;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;

/**
 * AdvancedQueue Queue Plugin for web build.
 *
 * @AdvancedQueueJobType(
 *  id = "va_gov_web_builder",
 *  label = @Translation("VA Web build queue"),
 *  max_retries = 0,
 *  retry_delay = 1
 * )
 */
class WebBuildJobType extends JobTypeBase implements ContainerFactoryPluginInterface {
  use CommandRunner;
  use LoggerChannelTrait;

  public const QUEUE_ID = 'va_gov_web_builder';

  /**
   * Logger Channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Web Build Status.
   *
   * @var \Drupal\va_gov_build_trigger\WebBuildStatusInterface
   */
  protected $webBuildStatus;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   * @param \Drupal\va_gov_build_trigger\WebBuildStatusInterface $webBuildStatus
   *   The web build status.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    LoggerChannelFactoryInterface $loggerFactory,
    WebBuildStatusInterface $webBuildStatus
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->setLoggerFactory($loggerFactory);
    $this->logger = $this->getLogger($this->getPluginId());
    $this->webBuildStatus = $webBuildStatus;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('va_gov.build_trigger.web_build_status')
    );
  }

  /**
   * Callback for the symfony process.
   *
   * @param \Symfony\Component\Process\Process $process
   *   The process object.
   */
  protected function processCallback(Process $process) : void {
    $this->logger->info(nl2br($process->getOutput()));
  }

  /**
   * {@inheritdoc}
   */
  public function process(Job $job) {
    if ($this->webBuildStatus->getWebBuildStatus()) {
      $delay = 30;
      $message = "Frontend build already in process... retrying in {$delay} seconds...";
      $this->logger->info($message);
      return JobResult::failure($message, 1, $delay);
    }
    // Blank the message to remove details of previous failures.
    $job->setMessage('');
    $this->webBuildStatus->enableWebBuildStatus();
    $this->logger->info('Starting front end rebuild.');

    $payload = $job->getPayload();

    $commands = $payload['commands'] ?? [];
    $messages = $this->runCommands($commands, 1, 0, [$this, 'processCallback']);

    if ($messages) {
      foreach ($messages as $message) {
        $this->logger->error(nl2br($message));
      }

      $this->logBrokenLinks();
      $this->webBuildStatus->disableWebBuildStatus();
      return JobResult::failure();
    }

    $this->logBrokenLinks();
    $this->logger->info('Front end has been successfully rebuilt.');
    $this->webBuildStatus->disableWebBuildStatus();
    return JobResult::success();
  }

  /**
   * Log Broken links if they exist.
   */
  protected function logBrokenLinks() : void {

    $contents = '{"summary":"*`campaign-mission-act`* : \n```<a class=\"vads-c-action-link--blue\" href rel=\"noreferrer noopener\" target=\"_blank\" id=\"a85fcc36ed81ab70421ca9977c5ec256\">\n                See more stories\n              </a>```\n```<a href=\"/outreach-and-events/events/veterans-town-hall-in-pittsburgh-pa-0\" id=\"8987ac5f258d32380703c3678a8b3a15\">\n                      Veterans town hall in Pittsburgh, PA\n                    </a>```\n```<a href=\"/outreach-and-events/events/facebook-live-community-care-info-session-0\" id=\"0dc1302ad74844b2fbdc726eeb672b6c\">\n                      Facebook Live: Community care info session\n                    </a>```","isHomepageBroken":false,"brokenLinksCount":3}';
    $json = Json::decode($contents);

    $client = new \Github\Client();
    $markdown = new Markdown($client);
    $this->logger->info(nl2br("There are {$json['brokenLinksCount']} broken links"));
    $this->logger->info(nl2br($markdown->render($json['summary'])));
  }

}
