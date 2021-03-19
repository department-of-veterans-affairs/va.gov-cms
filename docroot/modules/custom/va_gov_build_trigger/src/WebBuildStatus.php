<?php

namespace Drupal\va_gov_build_trigger;

use Drupal\Core\State\StateInterface;

/**
 * Status of Web Build.
 */
class WebBuildStatus implements WebBuildStatusInterface {

  /**
   * The state provider.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $stateProvider;

  /**
   * Web Build Status Key.
   *
   * @var string;
   */
  protected const STATE = 'va.web_build_status';

  /**
   * WebBuildStatus constructor.
   *
   * @param \Drupal\Core\State\StateInterface $stateProvider
   *   The drupal state provider.
   */
  public function __construct(StateInterface $stateProvider) {
    $this->stateProvider = $stateProvider;
  }

  /**
   * {@inheritDoc}
   */
  public function getWebBuildStatus() : bool {
    return (bool) $this->stateProvider->get(static::STATE, FALSE);
  }

  /**
   * {@inheritDoc}
   */
  public function enableWebBuildStatus() : void {
    $this->stateProvider->set(static::STATE, TRUE);
  }

  /**
   * {@inheritDoc}
   */
  public function disableWebBuildStatus() : void {
    $this->stateProvider->set(static::STATE, FALSE);
  }

}
