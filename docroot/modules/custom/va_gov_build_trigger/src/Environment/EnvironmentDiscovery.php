<?php

namespace Drupal\va_gov_build_trigger\Environment;

use Drupal\Core\Site\Settings;

/**
 * A class responsible for environment (dev/stage/prod) discovery.
 */
class EnvironmentDiscovery {

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
   * @param \Drupal\va_gov_build_trigger\Environment\EnvironmentManager $environmentManager
   *   The environment manager class.
   */
  public function __construct(EnvironmentManager $environmentManager) {
    $this->environmentManager = $environmentManager;
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
    $gha_deploy_environment = Settings::get('github_actions_deploy_env');
    return !empty($gha_deploy_environment) &&
      $this->getBuildTypeKey() === 'brd' &&
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
    return $this->getBuildTypeKey() === 'local';
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
   * @return string
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
   * Should the front end build be triggered from a content edit?
   *
   * @return bool
   *   Whether the front end build should be triggered.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function contentEditsShouldTriggerFrontendBuild() : bool {
    return $this->getEnvironment()->contentEditsShouldTriggerFrontendBuild();
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

  /**
   * Returns the Build Trigger Form class for the current environment.
   *
   * @return string
   *   Class name.
   */
  public function getBuildTriggerFormClass() : string {
    return $this->getEnvironment()->getBuildTriggerFormClass();
  }

  /**
   * Determine whether or not build log and frontend version are displayed.
   *
   * This mainly affects the content release form at /admin/content/deploy.
   *
   * @return bool
   *   TRUE if build details should be displayed.
   */
  public function shouldDisplayBuildDetails() : bool {
    return $this->getEnvironment()->shouldDisplayBuildDetails();
  }

}
