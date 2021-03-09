<?php

namespace Drupal\va_gov_build_trigger\SiteStatus;

use Drupal\Core\State\StateInterface;

/**
 * SiteStatus Service.
 */
class SiteStatus implements SiteStatusInterface {

  public const VA_GOV_DEPLOY_MODE = 'va_gov.deploy_mode';

  /**
   * State object.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * SiteStatus constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The State.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritDoc}
   */
  public function getDeployMode(): bool {
    return $this->state->get(static::VA_GOV_DEPLOY_MODE, FALSE);
  }

  /**
   * {@inheritDoc}
   */
  public function setDeployMode(bool $mode): void {
    $this->state->set(static::VA_GOV_DEPLOY_MODE, $mode);
  }

  /**
   * {@inheritDoc}
   */
  public function enableDeployMode(): void {
    $this->setDeployMode(TRUE);
  }

  /**
   * {@inheritDoc}
   */
  public function disableDeployMode(): void {
    $this->setDeployMode(FALSE);
  }

}
