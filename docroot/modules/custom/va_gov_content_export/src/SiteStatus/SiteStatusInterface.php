<?php

namespace Drupal\va_gov_content_export;

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
