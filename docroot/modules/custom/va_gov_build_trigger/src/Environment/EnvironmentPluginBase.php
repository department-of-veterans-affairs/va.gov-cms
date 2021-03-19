<?php

namespace Drupal\va_gov_build_trigger\Environment;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Site\Settings;
use Drupal\va_gov_build_trigger\WebBuildCommandBuilderInterface;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;
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
   * The WebStatus service.
   *
   * @var \Drupal\va_gov_build_trigger\WebBuildStatusInterface
   */
  protected $webBuildStatus;

  /**
   * Web build command builder.
   *
   * @var \Drupal\va_gov_build_trigger\WebBuildCommandBuilderInterface
   */
  protected $webBuildCommandBuilder;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LoggerInterface $logger,
    WebBuildStatusInterface $webBuildStatus,
    WebBuildCommandBuilderInterface $webBuildCommandBuilder
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
    $this->webBuildStatus = $webBuildStatus;
    $this->webBuildCommandBuilder = $webBuildCommandBuilder;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')->get('va_gov_build_trigger'),
      $container->get('va_gov.build_trigger.web_build_status'),
      $container->get('va_gov.build_trigger.web_build_command_builder')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getWebUrl(): string {
    return Settings::get('va_gov_frontend_url') ?? 'https://www.va.gov';
  }

  /**
   * {@inheritDoc}
   */
  protected function getWebBuildCommandBuilder(): WebBuildCommandBuilderInterface {
    return $this->webBuildCommandBuilder;
  }

}
