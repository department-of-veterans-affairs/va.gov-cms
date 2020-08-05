<?php

namespace Drupal\va_gov_content_export\SiteStatus;

/**
 * Interface SiteStatusInterface.
 */
interface SiteStatusInterface {

  /**
   * Is the site currently in Deploy Mode.
   *
   * @return bool
   *   In Deploy Mode.
   */
  public function inDeployMode() : bool;

}
