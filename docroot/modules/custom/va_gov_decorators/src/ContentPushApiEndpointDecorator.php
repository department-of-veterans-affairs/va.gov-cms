<?php

namespace Drupal\va_gov_decorators;

use Drupal\content_push_api\Service\Endpoint;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class ContentPushApiEndpointDecorator.
 *
 * Provides a place to set application-specific values for content_push_api.
 *
 * @package Drupal\va_gov_decorators
 */
class ContentPushApiEndpointDecorator extends Endpoint {

  /**
   * Set an endpoint for the request based on queue item endpoint_key value.
   *
   * @param string $endpoint_key
   *   The endpoint_key.
   *
   * @return bool|string
   *   The endpoint url w/ placeholder used for modifier inclusion.
   */
  public function endpointConstructor(string $endpoint_key) {
    $host = $this->endpointHost();

    switch ($endpoint_key) {
      // @todo: set this as a constant w/ decent description.
      case 'CMS_FACILITY_WRITE':
        $endpoint = $host . '/services/va_facilities/v0/facilities/%MODIFIER%/cms-overlay';
        break;

      default:
        $endpoint = FALSE;
        break;
    }

    return $endpoint;
  }

  /**
   * Returns endpoint_key based on entity bundle type.
   */
  public function endpointKey(EntityInterface $entity) {
    $endpoint_key = '';
    $config = \Drupal::config('content_push_api.settings');
    $bundles = $config->get('content_types');

    if ($entity->getEntityTypeId() === 'node' && in_array($entity->bundle(), $bundles)) {
      // @todo: set this as a constant w/ decent description.
      $endpoint_key = 'CMS_FACILITY_WRITE';
    }

    return $endpoint_key;
  }

  /**
   * Returns endpoint modifier based on entity field value.
   */
  public function endpointModifier(EntityInterface $entity) {
    $modifier = '';

    if ($entity->hasField('field_facility_locator_api_id')) {
      $modifier = !empty($entity->field_facility_locator_api_id->value) ?
        $entity->field_facility_locator_api_id->value : NULL;
    }

    return $modifier;
  }

  /**
   * Replaces modifier placeholder within the endpoint url.
   */
  public function endpoint(string $endpoint_key = NULL, string $modifier = NULL) {
    // Replace "%MODIFIER%" placeholder with an actual modifier.
    // Return FALSE if modifier is empty.
    if ($modifier) {
      $endpoint = str_ireplace("%MODIFIER%", $modifier, $this->endpointConstructor($endpoint_key));
    }
    else {
      $endpoint = FALSE;
    }

    return $endpoint;
  }

}
