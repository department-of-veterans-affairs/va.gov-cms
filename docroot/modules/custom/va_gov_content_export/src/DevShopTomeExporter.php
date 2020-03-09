<?php

namespace Drupal\va_gov_content_export;

/**
 * Tome Exporter only for DevShop.
 *
 * @package Drupal\va_gov_content_export
 */
class DevShopTomeExporter extends TomeExporter {

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
    @mkdir($directory);
    $handle = fopen($destination, 'c+');
    if (!flock($handle, LOCK_EX)) {
      throw new \Exception('Unable to acquire lock for the index file.');
    }
    return $handle;
  }

}
