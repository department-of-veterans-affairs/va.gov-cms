<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface to check the status of va.gov URLs.
 */
interface VaGovUrlInterface {

  /**
   * Get the va.gov front-end URL.
   *
   * @return string
   *   va.gov front-end URL.
   */
  public function getVaGovFrontEndUrl() : string;

  /**
   * Get the va.gov front-end URL for an entity.
   *
   * @return string
   *   va.gov front-end URL for entity.
   */
  public function getVaGovFrontEndUrlForEntity(EntityInterface $entity) : string;

  /**
   * Get the status for a va.gov URL.
   *
   * @return bool
   *   va.gov URL status.
   */
  public function vaGovFrontEndUrlIsLive(string $va_gov_url) : bool;

}
