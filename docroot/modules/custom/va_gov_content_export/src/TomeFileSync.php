<?php

namespace Drupal\va_gov_content_export;

use Drupal\Core\Config\StorageException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Site\Settings;
use Drupal\tome_sync\FileSync;

/**
 * Overriding FileSync to allow for .htaccess file to not be private.
 *
 * The CI system does not delete this file correctly due to read only
 * permissions so we override the protected nature to allow it to be deleted.
 */
class TomeFileSync extends FileSync {

  /**
   * The filesystem service.
   *
   * @var Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Class constructor.
   */
  public function __construct(FileSystemInterface $fileSystem) {
    $this->fileSystem = $fileSystem;
  }

  /**
   * {@inheritDoc}
   */
  protected function getFileDirectory() {
    // Overriding to remove "public" from the path.
    return Settings::get('tome_files_directory', '../files');
  }

  /**
   * Ensures that the file directory exists.
   */
  protected function ensureFileDirectory() {
    $file_directory = $this->getFileDirectory();
    $this->fileSystem->prepareDirectory($file_directory, FileSystemInterface::CREATE_DIRECTORY);

    // Here is the overridden line of code.  Added FALSE.
    // file_save_htaccess($file_directory, FALSE);.
    if (!file_exists($file_directory)) {
      throw new StorageException('Failed to create config directory ' . $file_directory);
    }
  }

}
