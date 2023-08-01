<?php

namespace Drupal\va_gov_content_release\FrontendVersion;

use Drupal\Core\State\StateInterface;

/**
 * The FrontendVersion service.
 *
 * This service allows (in some environments) control over the version of the
 * frontend that is used to perform content releases.
 */
class FrontendVersion implements FrontendVersionInterface {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * Get the current version of the frontend.
   *
   * @return string
   *   The current version of the frontend.
   */
  public function get() : string {
    return $this->state->get(FrontendVersionInterface::FRONTEND_VERSION, FrontendVersionInterface::FRONTEND_VERSION_DEFAULT);
  }

  /**
   * Set the current version of the frontend.
   *
   * @param string $version
   *   The version to set.
   */
  public function set(string $version) : void {
    $this->state->set(FrontendVersionInterface::FRONTEND_VERSION, $version);
  }

  /**
   * Reset the current version of the frontend.
   */
  public function reset() : void {
    $this->state->delete(FrontendVersionInterface::FRONTEND_VERSION);
  }

}
