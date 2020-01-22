<?php

namespace Drupal\va_gov_content_export\Normalizer;

use Drupal\tome_sync\Normalizer\ContentEntityNormalizer as TomeSyncContentEntityNormalizer;

/**
 * Normalizes/denormalizes Drupal content entities into an array structure.
 *
 * @internal
 */
class ContentEntityNormalizer extends TomeSyncContentEntityNormalizer {

  /**
   * Field names that should be excluded from normalization.
   *
   * Should only be used when more generic logic cannot be used.
   *
   * @var array
   */
  protected $fieldDenyList = [
    // Add metatags back in. Leave this commented out.
//    'metatag',
  ];
}
