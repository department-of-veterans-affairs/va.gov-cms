<?php

namespace Drupal\va_gov_build_trigger\FrontendBuild;

/**
 * Frontend Build status.
 */
interface StatusInterface {

  /**
   * Get the status of the frontend build.
   *
   * @return bool
   *   TRUE if active, otherwise FALSE.
   */
  public function getStatus() : bool;

  /**
   * Set the status of the frontend build.
   *
   * @param bool $status
   *   TRUE if active, otherwise FALSE.
   */
  public function setStatus(bool $status) : void;

  /**
   * Should Content Export be used?
   *
   * @return bool
   *   Should Content Export be used?
   */
  public function useContentExport() : bool;

}
