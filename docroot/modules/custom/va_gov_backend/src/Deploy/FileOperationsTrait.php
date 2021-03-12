<?php

namespace Drupal\va_gov_backend\Deploy;

use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;

/**
 * A trait for file operations while in Deploy mode.
 */
trait FileOperationsTrait {

  /**
   * Register the stream wrapper to be used later.
   */
  protected function registerStreamWrapper() : void {
    $output_uri = $this->getOutputUri();
    $streamWrapperInstance = new PublicStream();
    $streamWrapperInstance->setUri($output_uri);
    $this->setStreamWrapper($streamWrapperInstance);
  }

  /**
   * Does the tar archive file exist?
   *
   * @return bool
   *   Does the file exist.
   */
  protected function fileExists() : bool {
    $file_name = $this->getFilePath();
    return file_exists($file_name);
  }

  /**
   * Get the file path to the tar archive.
   *
   * @return null|string
   *   Either the local path to the file or null.
   */
  protected function getFilePath() : ?string {
    $streamWrapper = $this->getStreamWrapper();
    if ($streamWrapper) {
      return $streamWrapper->realpath();
    }

    return NULL;
  }

  /**
   * Get the URl to the file.
   *
   * @return string
   *   The url.
   */
  protected function getUrl() : string {
    return $this->getStreamWrapper()->getExternalUrl();
  }

  /**
   * Read the file contents into a string.
   *
   * @return string
   *   The contents of the file.
   */
  protected function readFile() : string {
    $path = $this->getFilePath();
    return file_get_contents($path);
  }

  /**
   * Get the stream wrapper instance.
   *
   * @reutrn Drupal\Core\StreamWrapper\StreamWrapperInterface
   *  The stream wrapper.
   */
  abstract protected function getStreamWrapper() : StreamWrapperInterface;

  /**
   * Get the stream wrapper class.
   *
   * @param \Drupal\Core\StreamWrapper\StreamWrapperInterface $streamWrapper
   *   Steam Warpper class.
   */
  abstract protected function setStreamWrapper(StreamWrapperInterface $streamWrapper) : void;

  /**
   * Get the URI to use in the output.
   *
   * @return string
   *   The url.
   */
  abstract protected function getOutputUri() : string;

}
