<?php

namespace Drupal\va_gov_media\Service;

/**
 * Interface for deleting PDFs that are not attached to content.
 */
interface VamcPdfDeleteInterface {

  /**
   * Find and delete PDFs that are not attached to content.
   */
  public function vamcPdfDelete();

}
