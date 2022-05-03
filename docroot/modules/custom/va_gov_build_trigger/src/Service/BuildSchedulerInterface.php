<?php

namespace Drupal\va_gov_build_trigger\Service;

/**
 * The interface for the build scheduler service.
 */
interface BuildSchedulerInterface {

  /**
   * Request a scheduled build if appropriate.
   *
   * This method will do nothing if the following criteria are not met:
   *   * It is currently during business hours (ET)
   *   * The last scheduled build was more than 60 minutes ago.
   */
  public function checkScheduledBuild() : void;

}
