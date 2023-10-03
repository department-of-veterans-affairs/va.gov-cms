<?php

namespace Drupal\va_gov_content_release\Commands;

use Drupal\va_gov_content_release\Frontend\Frontend;
use Drupal\va_gov_content_release\FrontendVersion\FrontendVersionInterface;
use Drush\Commands\DrushCommands;

/**
 * A Drush interface to the frontend version service.
 */
class FrontendVersionCommands extends DrushCommands {

  /**
   * The Frontend Version service.
   *
   * @var \Drupal\va_gov_content_release\FrontendVersion\FrontendVersionInterface
   */
  protected $frontendVersion;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_content_release\FrontendVersion\FrontendVersionInterface $frontendVersion
   *   The frontend version service.
   */
  public function __construct(
    FrontendVersionInterface $frontendVersion
  ) {
    $this->frontendVersion = $frontendVersion;
  }

  /**
   * Get the frontend version.
   *
   * @param string $frontend
   *   The frontend whose version we are getting.
   *
   * @command va-gov-content-release:frontend-version:get
   * @aliases va-gov-content-release-frontend-version-get
   */
  public function get(string $frontend = 'content_build') {
    $frontend = Frontend::fromRawValue($frontend);
    $value = $this->frontendVersion->getVersion($frontend);
    $this->io()->write($value);
  }

  /**
   * Reset the frontend version.
   *
   * @param string $frontend
   *   The frontend whose version we are resetting.
   *
   * @command va-gov-content-release:frontend-version:reset
   * @aliases va-gov-content-release-frontend-version-reset
   */
  public function reset(string $frontend = 'content_build') {
    $frontend = Frontend::fromRawValue($frontend);
    $this->frontendVersion->resetVersion($frontend);
    $this->io()->success('Frontend version reset.');
  }

  /**
   * Set the frontend version.
   *
   * @param string $frontend
   *   The frontend whose version we are getting.
   * @param string $version
   *   The version to set.
   *
   * @command va-gov-content-release:frontend-version:set
   * @aliases va-gov-content-release-frontend-version-set
   */
  public function setVersion(string $frontend = 'content_build', $version = '_default') {
    $frontend = Frontend::fromRawValue($frontend);
    $this->frontendVersion->setVersion($frontend, $version);
    $this->io()->success('Frontend version set to ' . $version);
  }

}
