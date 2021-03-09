<?php

namespace Drupal\va_gov_build_trigger\Commands;

use Drush\Commands\DrushCommands;
use Drupal\va_gov_build_trigger\FrontendBuild\StatusInterface;

/**
 * A Drush interface to the Frontend Build Status service.
 */
class FrontendBuildStatusCommands extends DrushCommands {

  /**
   * The Web Build Status service.
   *
   * @var \Drupal\va_gov_build_trigger\FrontendBuild\StatusInterface
   */
  protected $status;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_build_trigger\FrontendBuild\StatusInterface $status
   *   The frontend build status service.
   */
  public function __construct(StatusInterface $status) {
    $this->status = $status;
  }

  /**
   * Get the frontend build status.
   *
   * @command va-gov:get-frontend-build-status
   * @aliases va-gov-get-web-build-status
   *   va-gov-get-frontend-build-status
   */
  public function getStatus() {
    echo ($this->status->getStatus() ? 'ACTIVE' : 'INACTIVE') . PHP_EOL;
  }

  /**
   * Set the frontend build status.
   *
   * @param string $status
   *   Will be coerced to an int or string.
   *
   * @command va-gov:set-frontend-build-status
   * @aliases va-gov-set-web-build-status
   *   va-gov-set-frontend-build-status
   */
  public function setStatus(string $status) {
    $status = filter_var($status, FILTER_VALIDATE_BOOLEAN);
    $this->status->setStatus($status);
    if ($this->status->getStatus() !== $status) {
      throw new \Exception('Failed to set Frontend Build Status.');
    }
    $this->logger->success(dt('Frontend Build Status is currently :status.', [
      ':status' => ($status ? 'active' : 'inactive'),
    ]));
  }

}
