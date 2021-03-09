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
    echo ($this->siteStatus->getDeployMode() ? 'TRUE' : 'FALSE') . PHP_EOL;
  }

  /**
   * Set the deploy mode.
   *
   * @param string $mode
   *   Will be coerced to an int or string.
   *
   * @command va-gov:set-deploy-mode
   * @aliases va-gov-set-deploy-mode
   */
  public function setDeployMode(string $mode) {
    $mode = filter_var($mode, FILTER_VALIDATE_BOOLEAN);
    $this->siteStatus->setDeployMode($mode);
    if ($this->siteStatus->getDeployMode() !== $mode) {
      throw new \Exception('Failed to set Deploy Mode.');
    }
    $this->logger->success(dt('Deploy Mode is currently :mode.', [
      ':mode' => ($mode ? 'enabled' : 'disabled'),
    ]));
  }

  /**
   * Enable the deploy mode.
   *
   * @command va-gov:enable-deploy-mode
   * @aliases va-gov-enable-deploy-mode
   */
  public function enableDeployMode() {
    $this->setDeployMode('TRUE');
  }

  /**
   * Disable the deploy mode.
   *
   * @command va-gov:disable-deploy-mode
   * @aliases va-gov-disable-deploy-mode
   */
  public function disableDeployMode() {
    $this->setDeployMode('FALSE');
  }

}
