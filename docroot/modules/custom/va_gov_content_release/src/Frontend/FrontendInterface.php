<?php

namespace Drupal\va_gov_content_release\Frontend;

/**
 * Interface for the Frontend enum.
 */
interface FrontendInterface {

  /**
   * Get the raw value of the current frontend.
   *
   * @return string
   *   The raw value of the current frontend.
   */
  public function getRawValue() : string;

  /**
   * Check if the frontend is `content-build`.
   *
   * @return bool
   *   TRUE if the current frontend is `content-build`, FALSE otherwise.
   */
  public function isContentBuild() : bool;

  /**
   * Check if the frontend is `vets-website`.
   *
   * @return bool
   *   TRUE if the current frontend is `vets-website`, FALSE otherwise.
   */
  public function isVetsWebsite() : bool;

  /**
   * Check if the frontend is `next-build`.
   *
   * @return bool
   *   TRUE if the current frontend is `next-build`, FALSE otherwise.
   */
  public function isNextBuild() : bool;

}
