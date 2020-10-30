<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface VaGovUrlInterface.
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
   * Get the va.gov URL status for an entity.
   *
   * @return bool
   *   va.gov URL status.
   */
  public function vaGovFrontEndUrlForEntityIsLive(EntityInterface $entity) : bool;

}
