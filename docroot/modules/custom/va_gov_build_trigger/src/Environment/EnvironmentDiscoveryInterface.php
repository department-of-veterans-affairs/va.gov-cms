<?php

namespace Drupal\va_gov_build_trigger\Environment;

/**
 * A class responsible for environment (dev/stage/prod) discovery.
 */
interface EnvironmentDiscoveryInterface {

  /**
   * Is this on the BRD system?
   *
   * @return bool
   *   Is this on the BRD system?
   */
  public function isBrd() : bool;

  /**
   * Is this on Devshop?
   *
   * @return bool
   *   Is this on Devshop?
   */
  public function isDevShop() : bool;

  /**
   * Is this on Tugboat?
   *
   * @return bool
   *   Is this on Tugboat?
   */
  public function isTugboat() : bool;

  /**
   * Is this on Local?
   *
   * @return bool
   *   Is this on Local?
   */
  public function isLocal() : bool;

  /**
   * Get the environment type.
   *
   * @return string
   *   The name of the environment.
   */
  public function getEnvironmentId() : string;

  /**
   * Get the front end build type key.
   *
   * @return string|null
   *   The key defined in settings.php for the front end build.
   */
  public function getBuildTypeKey() : string;

  /**
   * Get the Environment Object.
   *
   * @return \Drupal\va_gov_build_trigger\Environment\EnvironmentInterface
   *   The Environment object.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   The exception if the plugin can not be loaded.
   */
  public function getEnvironment() : EnvironmentInterface;

  /**
   * Get the WEB Url for a desired environment type.
   *
   * @return string
   *   The location of the frontend web for the environment.
   */
  public function getWebUrl() : string;

  /**
   * Should the front end build be triggered?
   *
   * @return bool
   *   Whether the front end build should be triggered.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function shouldTriggerFrontendBuild() : bool;

  /**
   * Trigger a front end content build.
   *
   * @param string $front_end_git_ref
   *   Front end git reference to build (branch name or PR number)
   * @param bool $full_rebuild
   *   Trigger a full rebuild of the content.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function triggerFrontendBuild(string $front_end_git_ref = NULL, bool $full_rebuild = FALSE) : void;

  /**
   * Returns the Build Trigger Form class for the current environment.
   *
   * @return string
   *   Class name.
   */
  public function getBuildTriggerFormClass() : string;

}
