<?php

namespace Drupal\va_gov_notifications\Service;

/**
 * Checks for outdated content and queues notifications to editors.
 */
interface OutdatedContentInterface {

  /**
   * Queues notifications for editors that have outdated content.
   *
   * @param string $product_name
   *   The product name to connect to the product id.
   * @param string $template_name
   *   The machine name of the email template.
   *
   * @return array['editor' => string,'section' => string]
   *   An array of editor names and section for logging purposes only.
   */
  public function queueOutdatedContentNotifications(string $product_name, string $template_name);

}
