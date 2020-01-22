<?php

namespace Drupal\va_gov_content_export\Normalizer;

use Drupal\serialization\Normalizer\FieldItemNormalizer as BaseFieldItemNormalizer;

/**
 * Normalizer for fields.
 *
 * @internal
 */
class FieldItemNormalizer extends BaseFieldItemNormalizer {
  // Override the removal of the "processed" field in tome_sync. We need it.
}
