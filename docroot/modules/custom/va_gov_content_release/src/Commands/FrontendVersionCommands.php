<?php

namespace Drupal\va_gov_content_release\Commands;

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
   * @command va-gov-content-release:frontend-version:get
   * @aliases va-gov-content-release-frontend-version-get
   */
  public function get() {
    $value = $this->frontendVersion->get();
    $this->io()->write($value);
  }

  /**
   * Reset the frontend version.
   *
   * @command va-gov-content-release:frontend-version:reset
   * @aliases va-gov-content-release-frontend-version-reset
   */
  public function reset() {
    $this->frontendVersion->reset();
    $this->io()->success('Frontend version reset.');
  }

  /**
   * Set the frontend version.
   *
   * @command va-gov-content-release:frontend-version:set
   * @aliases va-gov-content-release-frontend-version-set
   */
  public function set($version) {
    $this->frontendVersion->set($version);
    $this->io()->success('Frontend version set to ' . $version);
  }

}
