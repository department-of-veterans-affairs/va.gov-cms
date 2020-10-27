<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface VaGovUrlInterface.
 */
interface VaGovUrlInterface {

  /**
   * Get the va.gov URL for an environment.
   *
   * @return string
   *   va.gov URL.
   */
  public function getVaGovUrlForEnvironment(String $environment) : string;

  /**
   * Get the va.gov URL for an entity and optional environment.
   *
   * @return string
   *   va.gov URL.
   */
  public function getVaGovUrlForEntity(EntityInterface $entity, String $environment) : string;

}
