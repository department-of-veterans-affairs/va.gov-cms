<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\node\NodeInterface;

/**
 * Collects logic related to the "Publish Now" button.
 */
interface PublishNowInterface {

  /**
   * Can this node be published in its present form?
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node that may or may not correspond to a publishable page.
   *
   * @return bool
   *   TRUE if the node is publishable, otherwise FALSE.
   */
  public function canPublishNode(NodeInterface $node) : bool;

  /**
   * Returns the correct markup for the "Publish Now" button.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node that we wish to publish now.
   *
   * @return string
   *   HTML suitable for injecting into a render array.
   */
  public function getButtonMarkup(NodeInterface $node) : string;

}
