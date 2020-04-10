<?php

namespace Drupal\content_push_api\Service;

use Drupal\Core\Entity\EntityInterface;

/**
 * For preparing endpoint url.
 */
class Endpoint implements EndpointInterface {

  /**
   * The address of the endpoint host.
   *
   * @var string
   */
  private $endpointHost = NULL;

  /**
   * {@inheritDoc}
   */
  public function __construct() {
    // @todo: use DI.
    $this->config = \Drupal::config('content_push_api.settings');
    $this->endpointHost = $this->config->get('endpoint_host');
  }

  /**
   * {@inheritDoc}
   */
  public function endpointKey(EntityInterface $entity) {}

  /**
   * {@inheritDoc}
   */
  public function endpointModifier(EntityInterface $entity) {}

  /**
   * {@inheritDoc}
   */
  public function endpointHost() {
    return $this->endpointHost;
  }

  /**
   * {@inheritDoc}
   */
  public function endpoint(string $endpoint_key = NULL, string $modifier = NULL) {}

  /**
   * {@inheritDoc}
   */
  public function endpointConstructor(string $endpoint_key) {}

}
