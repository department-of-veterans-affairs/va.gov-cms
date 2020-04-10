<?php

namespace Drupal\content_push_api\Service;

use Drupal\Core\Entity\EntityInterface;

/**
 * For preparing JSON payload based on updated field values.
 */
class Payload implements PayloadInterface {

  /**
   * {@inheritDoc}
   */
  public function __construct() {}

  /**
   * {@inheritDoc}
   */
  public function payload(EntityInterface $entity) {}

}
