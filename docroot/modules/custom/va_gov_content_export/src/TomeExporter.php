<?php

namespace Drupal\va_gov_content_export;

use Drupal\Core\Site\Settings;
use Drupal\tome_sync\Exporter;

/**
 * Exporter class for Tome.
 *
 * Overridden to exclude more types of entities.
 */
class TomeExporter extends Exporter {
  /**
   * An array of excluded entity types.
   *
   * @var string[]
   */
  protected static $excludedTypes = [
    'content_moderation_state',
    'user',
    'user_role',
    'user_history',
  ];

  /**
   * Gets the index file path.
   *
   * @return string
   *   The index file path.
   */
  protected function getContentIndexFilePath() {
    return Settings::get('tome_content_directory', '../content') . '/meta_index.json';
  }

}
