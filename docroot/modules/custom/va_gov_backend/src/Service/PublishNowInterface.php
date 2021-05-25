<?php

namespace Drupal\va_gov_backend\Service;

use Drupal\node\NodeInterface;

/**
 * Collects logic related to the "Publish Now" button.
 */
interface PublishNowInterface {

  /**
   * Should we show a "Publish Now" button?
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node that may or may not correspond to a publishable page.
   *
   * @return bool
   *   TRUE if the button should be displayed, otherwise FALSE.
   */
  public function shouldDisplayButton(NodeInterface $node) : bool;

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
