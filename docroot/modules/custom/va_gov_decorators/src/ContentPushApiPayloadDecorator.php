<?php

namespace Drupal\va_gov_decorators;

use Drupal\content_push_api\Service\Payload;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class ContentPushApiPayloadDecorator.
 *
 * Provides a place to set application-specific values for content_push_api.
 *
 * @package Drupal\va_gov_decorators
 */
class ContentPushApiPayloadDecorator extends Payload {

  /**
   * Builds the queue item to be added.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   New or updated entity object used to process and extract only values
   *   that we need to send.
   *
   * @return array
   *   The payload data array that will be added to the queue item.
   */
  public function payload(EntityInterface $entity) {
    // Default payload is an empty array.
    $data = [];

    // Is entity new or updated?
    $original_entity = $entity->original;

    // Current field values.
    $operating_status = $entity->field_operating_status_facility->value;
    $additional_info = $entity->field_operating_status_more_info->value;

    if ($original_entity instanceof EntityInterface) {
      // Entity is updated.
      // @todo: Checks for updated field values should be abstracted eventually.
      $original_operating_status = $original_entity->field_operating_status_facility->value;
      $original_additional_info = $original_entity->field_operating_status_more_info->value;

      if ($operating_status !== $original_operating_status || $additional_info !== $original_additional_info) {
        // One of the status values changed. Form the payload.
        $data = [
          'operating_status' => [
            'code' => strtoupper($operating_status),
            'additional_info' => $additional_info,
          ],
        ];
      }
    }
    else {
      // Entity is new. Form the payload.
      $data = [
        'operating_status' => [
          'code' => strtoupper($operating_status),
          'additional_info' => $additional_info,
        ],
      ];
    }

    return $data;
  }

}
