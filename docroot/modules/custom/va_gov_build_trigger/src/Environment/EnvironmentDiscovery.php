<?php

namespace Drupal\va_gov_build_trigger\Environment;

use Drupal\Core\Site\Settings;

/**
 * A class responsible for environment (dev/stage/prod) discovery.
 */
class EnvironmentDiscovery implements EnvironmentDiscoveryInterface {

  /**
   * Drupal Settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * The current environment object.
   *
   * @var \Drupal\va_gov_build_trigger\Environment\EnvironmentInterface
   */
  protected $environment;

  /**
   * The Environment Plugin Provider.
   *
   * @var \Drupal\va_gov_build_trigger\Environment\EnvironmentManager
   */
  protected $environmentManager;

  /**
   * EnvironmentDiscovery constructor.
   *
   * @param \Drupal\Core\Site\Settings $settings
   *   The Drupal settings service.
   * @param \Drupal\va_gov_build_trigger\Environment\EnvironmentManager $environmentManager
   *   The environment manager class.
   */
  public function __construct(Settings $settings, EnvironmentManager $environmentManager) {
    $this->settings = $settings;
    $this->environmentManager = $environmentManager;
  }

  /**
   * {@inheritdoc}
   */
  public function isBrd() : bool {
    $jenkins_build_environment = $this->settings->get('jenkins_build_env');
    return !empty($jenkins_build_environment) &&
      $this->getBuildTypeKey() === 'brd' &&
      !$this->isCli();
  }

  /**
   * {@inheritdoc}
   */
  public function isDevShop() : bool {
    return $this->getBuildTypeKey() === 'devshop' &&
      class_exists('DevShopTaskApiClient') &&
      !$this->isCli();
  }

  /**
   * {@inheritdoc}
   */
  public function isTugboat() : bool {
    return $this->getBuildTypeKey() === 'tugboat' ||
      !empty(getenv('TUGBOAT_DEFAULT_SERVICE'));
  }

  /**
   * {@inheritdoc}
   */
  public function isLocal() : bool {
    return $this->getBuildTypeKey() === 'lando';
  }

  /**
   * {@inheritdoc}
   */
  public function getEnvironmentId() : string {
    return getenv('CMS_ENVIRONMENT_TYPE') ?: 'ci';
  }

  /**
   * {@inheritdoc}
   */
  public function getBuildTypeKey() : string {
    return $this->settings->get('va_gov_frontend_build_type', 'tugboat');
  }

  /**
   * {@inheritdoc}
   */
  public function getEnvironment() : EnvironmentInterface {
    if (!$this->environment) {
      $build_type = $this->getBuildTypeKey();
      $this->environment = $this->environmentManager->createInstance($build_type);
    }
    return $this->environment;
  }

  /**
   * {@inheritdoc}
   */
  public function getWebUrl() : string {
    return $this->getEnvironment()->getWebUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function shouldTriggerFrontendBuild() : bool {
    return $this->getEnvironment()->shouldTriggerFrontendBuild();
  }

  /**
   * {@inheritdoc}
   */
  public function triggerFrontendBuild(string $front_end_git_ref = NULL, bool $full_rebuild = FALSE) : void {
    $this->getEnvironment()->triggerFrontendBuild($front_end_git_ref, $full_rebuild);
  }

  /**
   * Returns whether the current PHP process runs on CLI.
   *
   * @return bool
   *   Current process is CLI.
   */
  protected function isCli() : bool {
    return PHP_SAPI === 'cli';
  }

  /**
   * {@inheritdoc}
   */
  public function getBuildTriggerFormClass() : string {
    return $this->getEnvironment()->getBuildTriggerFormClass();
  }

}
