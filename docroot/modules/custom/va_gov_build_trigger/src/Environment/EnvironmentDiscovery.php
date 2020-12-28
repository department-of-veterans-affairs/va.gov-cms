<?php

namespace Drupal\va_gov_build_trigger\Environment;

use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * A class responsible for environment (dev/stage/prod) discovery.
 */
class EnvironmentDiscovery {

  // Hosts we associate with the Prod BRD environment.
  public const VAGOV_PRODUCTION_HOSTS = [
    'cms.va.gov',
    'prod.cms.va.gov',
  ];

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
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * EnvironmentDiscovery constructor.
   *
   * @param \Drupal\va_gov_build_trigger\Environment\EnvironmentManager $environmentManager
   *   The environment manager class.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(EnvironmentManager $environmentManager, RequestStack $requestStack) {
    $this->environmentManager = $environmentManager;
    $this->request = $requestStack->getCurrentRequest();
  }

  /**
   * Is this on the BRD system?
   *
   * @return bool
   *   Is this on the BRD system?
   *
   * @codingStandardsIgnoreStart
   */
  public function isBRD() : bool {
    // @codingStandardsIgnoreEnd
    $jenkins_build_environment = Settings::get('jenkins_build_env');
    return !empty($jenkins_build_environment) &&
      $this->getBuildTypeKey() === 'brd' &&
      !$this->isCli();
  }

  /**
   * Is this on Devshop?
   *
   * @return bool
   *   Is this on Devshop?
   */
  public function isDevShop() : bool {
    return $this->getBuildTypeKey() === 'devshop' &&
      class_exists('DevShopTaskApiClient') &&
      !$this->isCli();
  }

  /**
   * Is this on Tugboat?
   *
   * @return bool
   *   Is this on Tugboat?
   */
  public function isTugboat() : bool {
    return $this->getBuildTypeKey() === 'tugboat' ||
      !empty(getenv('TUGBOAT_DEFAULT_SERVICE'));
  }

  /**
   * Is this on Local?
   *
   * @return bool
   *   Is this on Local?
   */
  public function isLocal() : bool {
    return $this->getBuildTypeKey() === 'lando';
  }

  /**
   * Is this on Production?
   *
   * @return bool
   *   Is this on Production?
   */
  public function isProd() : bool {
    return !empty($this->request) && in_array($this->request->getHost(), static::VAGOV_PRODUCTION_HOSTS);
  }

  /**
   * Get the environment type.
   *
   * @return string
   *   The name of the environment.
   */
  public function getEnvironmentId() : string {
    return getenv('CMS_ENVIRONMENT_TYPE') ?: 'ci';
  }

  /**
   * Get the front end build type key.
   *
   * @return string|null
   *   The key defined in settings.php for the front end build.
   */
  public function getBuildTypeKey() : string {
    return Settings::get('va_gov_frontend_build_type', 'tugboat');
  }

  /**
   * Get the Environment Object.
   *
   * @return \Drupal\va_gov_build_trigger\Environment\EnvironmentInterface
   *   The Environment object.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   The exception if the plugin can not be loaded.
   */
  public function getEnvironment() : EnvironmentInterface {
    if ($this->environment) {
      return $this->environment;
    }
    $build_type = $this->getBuildTypeKey();
    if ($build_type === 'brd' && $this->isProd()) {
      $build_type = 'production';
    }
    $this->environment = $this->environmentManager->createInstance($build_type);
    return $this->environment;
  }

  /**
   * Get the WEB Url for a desired environment type.
   *
   * @return string
   *   The location of the frontend web for the environment.
   */
  public function getWebUrl() : string {
    return $this->getEnvironment()->getWebUrl();
  }

  /**
   * Should the front end build be triggered?
   *
   * @return bool
   *   Whether the front end build should be triggered.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function shouldTriggerFrontendBuild() : bool {
    return $this->getEnvironment()->shouldTriggerFrontendBuild();
  }

  /**
   * Trigger a front end content build.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function triggerFrontendBuild() : void {
    $this->getEnvironment()->triggerFrontendBuild();
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

}
