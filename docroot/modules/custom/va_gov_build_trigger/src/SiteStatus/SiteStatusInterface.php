<?php

namespace Drupal\va_gov_build_trigger\SiteStatus;

/**
 * Interface SiteStatusInterface.
 */
interface SiteStatusInterface {

  /**
   * Is the site currently in Deploy Mode.
   *
   * Deploy mode is enabled when a new code and database changes occur.
   * At this point the site is in a state where unpredictable results could
   * occur to an end user.
   *
   * Deploy mode is disabled when the all code deploy, config import,
   * and database have been completed.
   *
   * @return bool
   *   In Deploy Mode.
   */
  public function inDeployMode() : bool;

  /**
   * Turn on Deploy Mode.
   */
  public function enableDeployMode() : void;

  /**
   * Turn off Deploy Mode.
   */
  public function disableDeployMode() : void;

}
