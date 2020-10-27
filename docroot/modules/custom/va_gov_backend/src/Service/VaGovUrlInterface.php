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

  /**
   * Get the va.gov URL status for an entity and optional environment.
   *
   * @return int
   *   va.gov URL status code.
   */
  public function getVaGovUrlStatusForEntity(EntityInterface $entity, String $environment) : int;

}
