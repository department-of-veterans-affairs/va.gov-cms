<?php

namespace Drupal\va_gov_notifications\Service;

/**
 * Checks for outdated content and queues notifications to editors.
 */
interface OutdatedContentInterface {

  /**
   * Queues notifications for VAMC editors with outdated content.
   *
   * @return array
   *   An array of editor names and section for logging purposes.
   */
  public function queueOutdatedVamcContentNotifications();

  /**
   * Queues notifications for Vet Center editors with outdated content.
   *
   * @return array
   *   An array of editor names and section for logging purposes.
   */
  public function queueOutdatedVetCenterContentNotifications();

}
