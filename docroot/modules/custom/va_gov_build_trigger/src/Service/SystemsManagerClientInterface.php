<?php

namespace Drupal\va_gov_build_trigger\Service;

/**
 * A client for interfacing with Jenkins.
 */
interface SystemsManagerClientInterface {

  /**
   * Gets the current Jenkins API token.
   *
   * @return string
   *   The value of the value of ssm param.
   */
  public function getJenkinsApiToken(): string;

}
