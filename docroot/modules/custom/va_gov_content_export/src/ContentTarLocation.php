<?php

namespace Drupal\va_gov_content_export;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Content Tar Location Service.
 *
 * Provides uri and urls to the export content tar.
 */
class ContentTarLocation implements ContentTarLocationInterface {
  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The file system class.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * ContentTarLocation constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config system class.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system class.
   */
  public function __construct(ConfigFactoryInterface $configFactory, FileSystemInterface $fileSystem) {
    $this->configFactory = $configFactory;
    $this->fileSystem = $fileSystem;
  }

  /**
   * Get the content url.
   *
   * @return string|null
   *   The uri to the content tar file.
   */
  public function getUri() : ?string {
    $path_to_tar_config = $this->configFactory->get('va_gov_content_export.settings');
    if (!$path_to_tar_config->get('content_tar_uri')) {
      return NULL;
    }

    return $path_to_tar_config->get('content_tar_uri');
  }

  /**
   * Get the path to the file.
   *
   * @return string
   *   The path to the content tar.
   */
  public function getFilePath() : string {
    $uri = $this->getUri();
    return $this->fileSystem->realpath($uri) ?? '';
  }

  /**
   * Get a files URL.
   *
   * @return string|null
   *   The URL of the file or NULL if not found.
   */
  public function getUrl() : ?string {
    $uri = $this->getUri();
    if (!$uri) {
      return NULL;
    }

    return file_create_url($uri);
  }

}
