<?php

namespace Drupal\content_push_api\Service;

use Drupal\Core\Entity\EntityInterface;

/**
 * For preparing endpoint url.
 */
class Endpoint implements EndpointInterface {

  /**
   * The config object for content_push_api.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $config = NULL;

  /**
   * The address of the endpoint host.
   *
   * @var string
   */
  private $endpointHost = NULL;

  /**
   * Endpoint constructor.
   */
  public function __construct() {
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
