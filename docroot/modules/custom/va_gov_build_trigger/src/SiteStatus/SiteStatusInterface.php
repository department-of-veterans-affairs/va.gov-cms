<?php

namespace Drupal\va_gov_build_trigger\SiteStatus;

/**
 * Interface SiteStatusInterface.
 */
interface SiteStatusInterface {

  /**
   * Is the site currently in Deploy Mode?
   *
   * Deploy mode is enabled when a new code and database changes occur.
   * At this point the site is in a state where unpredictable results could
   * occur to an end user.
   *
   * Deploy mode is disabled when the all code deploy, config import,
   * database update and cms bulk cms export have been completed.
   *
   * @return bool
   *   TRUE if in deploy mode, otherwise FALSE.
   */
  public function getDeployMode() : bool;

  /**
   * Set whether or not we are currently in deploy mode.
   *
   * @param bool $mode
   *   TRUE if we should be in deploy mode, otherwise FALSE.
   */
  public function setDeployMode(bool $mode) : void;

  /**
   * Turn on Deploy Mode.
   */
  public function enableDeployMode() : void;

  /**
   * Turn off Deploy Mode.
   */
  public function disableDeployMode() : void;

}
