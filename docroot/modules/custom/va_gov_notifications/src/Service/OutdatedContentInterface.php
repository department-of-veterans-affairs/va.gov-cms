<?php

namespace Drupal\va_gov_notifications\Service;

/**
 * Checks for outdated content and sends notifications to editors.
 */
interface OutdatedContentInterface {

  /**
   * Checks if every editor has outdated content.
   */
  public function checkForOutdatedContent();

}
