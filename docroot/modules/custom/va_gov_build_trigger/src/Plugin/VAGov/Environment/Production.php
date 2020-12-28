<?php

namespace Drupal\va_gov_build_trigger\Plugin\VAGov\Environment;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\slack\Slack;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Production BRD Plugin for Environment.
 *
 * @Environment(
 *   id = "production",
 *   label = @Translation("Production")
 * )
 */
class Production extends BRD {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

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
    ConfigFactoryInterface $config,
    Slack $slack
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $dateFormatter, $database);
    $this->config = $config;
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
      $container->get('config.factory'),
      $container->get('slack.slack_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function reportException(\Exception $exception) {
    parent::reportException($exception);
    $slackConfig = $this->config->get('slack.settings');
    if ($slackConfig->get('slack_webhook_url')) {
      $jenkins_build_job_url = Settings::get('jenkins_build_job_url');
      $message = $this->t('Site rebuild request has failed for :url with an Exception, check log for more information.', [
        ':url' => $jenkins_build_job_url,
      ]);
      $this->slack->sendMessage("@here :warning: $message");
    }
  }

}
