<?php

namespace Drupal\content_push_api\Service;

use Drupal\Core\Entity\EntityInterface;

/**
 * For preparing an endpoint for the request.
 */
interface EndpointInterface {

  /**
   * Returns endpoint key.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object. Based on entity properties/fields, you can set an
   *   endpoint key which can later be used to form an endpoint url specific to
   *   your entity properties, e.g. different content types may have different
   *   endpoints to post to.
   *
   * @return string
   *   Endpoint key that is added to queue item in order to dynamically
   *   generate endpoint for the request while queue item is being processed.
   */
  public function endpointKey(EntityInterface $entity);

  /**
   * Returns endpoint modifier.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object. Entity field values can be used as endpoint modifiers.
   *   E.g. if you need to append NID to the end of your endpoint, you'd
   *   return NID as a modifier.
   *
   *   See README.md.
   */
  public function endpointModifier(EntityInterface $entity);

  /**
   * Returns endpoint host.
   *
   * @return string
   *   Endpoint host set in Content Push API settings form.
   */
  public function endpointHost();

  /**
   * Returns fully qualified endpoint.
   *
   * @param string|null $endpoint_key
   *   Endpoint key that allows to use custom logic for forming full
   *   endpoint URL dynamically via custom Endpoint Decorator. See README.md.
   * @param string|null $modifier
   *   Endpoint modifier. E.g. entity id or similar value that needs to be
   *   appended to the endpoint in order to target correct entity.
   *
   * @return string
   *   Endpoint. Empty by default. Customizable via Endpoint service decorator.
   *   See README.md
   */
  public function endpoint(string $endpoint_key = NULL, string $modifier = NULL);

  /**
   * Returns endpoint url with a modifier placeholder.
   *
   * @param string $endpoint_key
   *   Endpoint key, used to form endpoint url.
   *
   * @return bool|string
   *   Returns fully formed endpoint url with a modifier placeholder, that
   *   can be replaced in $this->endpoint() method. Sample output should look
   *   similar to https://example.host/services/entity-type/%MODIFIER%/xyz.
   */
  public function endpointConstructor(string $endpoint_key);

}
