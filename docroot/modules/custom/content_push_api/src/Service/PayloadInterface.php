<?php

namespace Drupal\content_push_api\Service;

use Drupal\Core\Entity\EntityInterface;

/**
 * For preparing payload for the request.
 */
interface PayloadInterface {

  /**
   * Returns a payload to be assigned to a queue item.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Arbitrary data to be associated with the new item in the queue.
   *
   * @return array
   *   Payload. Empty array by default. Customizable via Payload service
   *   decorator.
   *   See README.md
   */
  public function payload(EntityInterface $entity);

}
