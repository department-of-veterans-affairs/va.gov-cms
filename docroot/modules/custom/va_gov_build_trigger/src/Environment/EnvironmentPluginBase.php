<?php

namespace Drupal\va_gov_build_trigger\Environment;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Site\Settings;

/**
 * Base Class used for Environment Plugins.
 */
abstract class EnvironmentPluginBase extends PluginBase implements EnvironmentInterface {
  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The WebStatus service.
   *
   * @var \Drupal\va_gov_build_trigger\WebBuildStatus
   */
  protected $webBuildStatus;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = \Drupal::logger('va_gov_build_trigger');
    $this->webBuildStatus = \Drupal::service('va_gov.build_trigger.web_build_status');
  }

  /**
   * {@inheritDoc}
   */
  public function getWebUrl(): string {
    return Settings::get('va_gov_frontend_url');
  }

}
