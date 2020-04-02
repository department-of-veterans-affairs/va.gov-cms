<?php

namespace Drupal\va_gov_content_export;

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
   * Acquires a lock for writing to the index.
   *
   * @return resource
   *   A file pointer resource on success.
   *
   * @throws \Exception
   *   Throws an exception when the index file cannot be written to.
   */
  protected function acquireContentIndexLock() {
    $destination = $this->getContentIndexFilePath();
    $directory = dirname($destination);
    // Overridding how the directory is being created because we want to
    // use the default parent directory permission
    // for devshop tests to work correctly.
    @mkdir($directory);
    $handle = fopen($destination, 'c+');
    if (!flock($handle, LOCK_EX)) {
      throw new \Exception('Unable to acquire lock for the index file.');
    }
    return $handle;
  }

}
