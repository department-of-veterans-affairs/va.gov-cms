<?php

namespace Drupal\va_gov_build_trigger\Commands;

use Drush\Commands\DrushCommands;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;

/**
 * A Drush interface to the Web Build Status service.
 */
class WebBuildStatusCommands extends DrushCommands {

  /**
   * The Web Build Status service.
   *
   * @var \Drupal\va_gov_build_trigger\WebBuildStatusInterface
   */
  protected $webBuildStatus;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_build_trigger\WebBuildStatusInterface $webBuildStatus
   *   The frontend build service.
   */
  public function __construct(WebBuildStatusInterface $webBuildStatus) {
    $this->webBuildStatus = $webBuildStatus;
  }

  /**
   * Get the web build status.
   *
   * @command va-gov:get-web-build-status
   * @aliases va-gov-get-web-build-status
   */
  public function getWebBuildStatus() {
    echo ($this->webBuildStatus->getWebBuildStatus() ? 'ENABLED' : 'DISABLED') . PHP_EOL;
  }

  /**
   * Set the web build status.
   *
   * @param string $status
   *   Will be coerced to an int or string.
   *
   * @command va-gov:set-web-build-status
   * @aliases va-gov-set-web-build-status
   */
  public function setWebBuildStatus(string $status) {
    $status = filter_var($status, FILTER_VALIDATE_BOOLEAN);
    $this->webBuildStatus->setWebBuildStatus($status);
    if ($this->webBuildStatus->getWebBuildStatus() !== $status) {
      throw new \Exception('Failed to set Web Build Status.');
    }
    $this->logger->success(dt('Web Build Status is now :status.', [
      ':status' => ($status ? 'active' : 'inactive'),
    ]));
  }

}
