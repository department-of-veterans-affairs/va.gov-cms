<?php

namespace Drupal\va_gov_content_types\Interfaces;

/**
 * Provides an interface for retrieving the content type of a node.
 */
interface GetTypeInterface {

  /**
   * Get the content type of this node.
   *
   * @return string
   *   The content type of this node.
   */
  public function getType();

}
