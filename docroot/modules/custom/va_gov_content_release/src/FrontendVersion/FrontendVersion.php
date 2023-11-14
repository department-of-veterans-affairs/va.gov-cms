<?php

namespace Drupal\va_gov_content_release\FrontendVersion;

use Drupal\Core\State\StateInterface;
use Drupal\va_gov_content_release\Frontend\FrontendInterface;

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
   * Get the key for the frontend version.
   *
   * @param \Drupal\va_gov_content_release\Frontend\FrontendInterface $frontend
   *   The frontend whose version we are getting or setting.
   *
   * @return string
   *   The key for the frontend version.
   */
  protected function getKey(FrontendInterface $frontend) : string {
    return FrontendVersionInterface::FRONTEND_VERSION_PREFIX . $frontend->getRawValue();
  }

  /**
   * {@inheritDoc}
   */
  public function getVersion(FrontendInterface $frontend) : string {
    $key = $this->getKey($frontend);
    return $this->state->get($key, FrontendVersionInterface::FRONTEND_VERSION_DEFAULT);
  }

  /**
   * {@inheritDoc}
   */
  public function setVersion(FrontendInterface $frontend, string $version) : void {
    $key = $this->getKey($frontend);
    $this->state->set($key, $version);
  }

  /**
   * {@inheritDoc}
   */
  public function resetVersion(FrontendInterface $frontend) : void {
    $key = $this->getKey($frontend);
    $this->state->delete($key);
  }

}
