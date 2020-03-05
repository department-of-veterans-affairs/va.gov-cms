<?php

namespace Drupal\va_gov_content_export;


use Drupal\Core\File\FileSystem as CoreFileSystem;

/**
 * Override how files are handled on Devshop.
 *
 * @package Drupal\va_gov_content_export
 */
class FileSystem extends CoreFileSystem {
  /**
   * Default mode for new directories. See self::chmod().
   */
  const CHMOD_DIRECTORY = 2770;

  /**
   * Default mode for new files. See self::chmod().
   */
  const CHMOD_FILE = 2664;

}
