<?php

namespace Drupal\va_gov_content_export\SiteStatus;

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
  public function inDeployMode(): bool {
    return $this->state->get(static::VA_GOV_DEPLOY_MODE, FALSE);
  }

  /**
   * {@inheritDoc}
   */
  public function enableDeployMode(): void {
    $this->state->set(static::VA_GOV_DEPLOY_MODE, TRUE);
  }

  /**
   * {@inheritDoc}
   */
  public function disableDeployMode(): void {
    $this->state->set(static::VA_GOV_DEPLOY_MODE, FALSE);
  }

}
