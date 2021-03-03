<?php

namespace Drupal\va_gov_build_trigger\Commands;

use Drush\Commands\DrushCommands;
use Drupal\va_gov_build_trigger\SiteStatus\SiteStatusInterface;

/**
 * A Drush interface to the Site Status service.
 */
class SiteStatusCommands extends DrushCommands {

  /**
   * The Site Status service.
   *
   * @var \Drupal\va_gov_build_trigger\SiteStatus\SiteStatusInterface
   */
  protected $siteStatus;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_build_trigger\SiteStatus\SiteStatusInterface $siteStatus
   *   The site status service.
   */
  public function __construct(SiteStatusInterface $siteStatus) {
    $this->siteStatus = $siteStatus;
  }

  /**
   * Get the deploy mode.
   *
   * @command va-gov:get-deploy-mode
   * @aliases va-gov-get-deploy-mode
   */
  public function getDeployMode() {
    echo ($this->siteStatus->inDeployMode() ? 'ENABLED' : 'DISABLED') . PHP_EOL;
  }

  /**
   * Enable the deploy mode.
   *
   * @command va-gov:enable-deploy-mode
   * @aliases va-gov-enable-deploy-mode
   */
  public function enableDeployMode() {
    $this->siteStatus->enableDeployMode();
    $this->logger->success(dt('Deploy mode has been enabled.'));
  }

  /**
   * Disable the deploy mode.
   *
   * @command va-gov:disable-deploy-mode
   * @aliases va-gov-disable-deploy-mode
   */
  public function disableDeployMode() {
    $this->siteStatus->disableDeployMode();
    $this->logger->success(dt('Deploy mode has been disabled.'));
  }

}
