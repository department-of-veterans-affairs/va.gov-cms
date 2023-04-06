<?php

namespace Drupal\va_gov_notifications\Service;

/**
 * Checks for outdated content and sends notifications to editors.
 */
interface OutdatedContentInterface {

  /**
   * Checks if VAMC editors have outdated content.
   */
  public function checkForOutdatedVamcContent();

  /**
   * Checks if Vet Center editors have outdated content.
   */
  public function checkForOutdatedVetCenterContent();

}
