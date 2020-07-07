<?php

namespace Drupal\va_gov_content_export\Event;

use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Wraps a node insertion demo event for event listeners.
 */
class ContentExportPreTarEvent extends Event {

  const CONTENT_EXPORT_PRE_TAR_EVENT = 'va_gov_content_export.pre.tar';

  /**
   * The path of files to be tarred.
   *
   * @var string
   */
  protected $tarPath;

  /**
   * A Drupal file system object.
   *
   * @var Drupal\Core\File\FileSystemInterface
   */
  public $fileSystem;

  /**
   * Constructs a va_gov_content_export event object.
   *
   * @param string $tar_path
   *   The directory that will be tarred.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   Drupal FileSystem.
   */
  public function __construct($tar_path, FileSystemInterface $file_system) {
    $this->tarPath = $tar_path;
    $this->fileSystem = $file_system;
  }

  /**
   * Getter for tarPath.
   *
   * @return string
   *   The path of files to be tarred.
   */
  public function getTarPath() {
    return $this->tarPath;
  }

}
