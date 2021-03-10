<?php

namespace Drupal\va_gov_build_trigger\Environment;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Site\Settings;
use Drupal\va_gov_build_trigger\FrontendBuild\Command\BuilderInterface as CommandBuilderInterface;
use Drupal\va_gov_build_trigger\FrontendBuild\Command\QueueInterface;
use Drupal\va_gov_build_trigger\FrontendBuild\StatusInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base Class used for Environment Plugins.
 */
abstract class EnvironmentPluginBase extends PluginBase implements EnvironmentInterface, ContainerFactoryPluginInterface {
  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * The Frontend Build status service.
   *
   * @var \Drupal\va_gov_build_trigger\FrontendBuild\StatusInterface
   */
  protected $status;

  /**
   * Web build command builder.
   *
   * @var \Drupal\va_gov_build_trigger\FrontendBuild\Command\BuilderInterface
   */
  protected $commandBuilder;

  /**
   * The queue service.
   *
   * @var \Drupal\va_gov_build_trigger\FrontendBuild\Command\QueueInterface
   */
  protected $queue;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LoggerInterface $logger,
    Settings $settings,
    StatusInterface $status,
    CommandBuilderInterface $commandBuilder,
    QueueInterface $queue
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
    $this->settings = $settings;
    $this->status = $status;
    $this->commandBuilder = $commandBuilder;
    $this->queue = $queue;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.channel.va_gov_build_trigger'),
      $container->get('settings'),
      $container->get('va_gov_build_trigger.frontend_build.status'),
      $container->get('va_gov_build_trigger.frontend_build.command.builder'),
      $container->get('va_gov_build_trigger.frontend_build.command.queue')
    );
  }

  /**
   * Access to the frontend build status service.
   *
   * @return \Drupal\va_gov_build_trigger\FrontendBuild\StatusInterface
   *   The frontend build status service.
   */
  protected function getFrontendBuildStatus(): StatusInterface {
    return $this->status;
  }

  /**
   * Access to the internal command builder.
   *
   * @return \Drupal\va_gov_build_trigger\FrontendBuild\Command\BuilderInterface
   *   The command builder.
   */
  protected function getWebBuildCommandBuilder(): CommandBuilderInterface {
    return $this->commandBuilder;
  }

  /**
   * Access to the queue service.
   *
   * @return \Drupal\va_gov_build_trigger\FrontendBuild\Command\QueueInterface
   *   The queue service.
   */
  protected function getQueue(): QueueInterface {
    return $this->queue;
  }

  /**
   * {@inheritDoc}
   */
  public function getWebUrl(): string {
    return $this->settings->get('va_gov_frontend_url') ?? 'https://www.va.gov';
  }

}
