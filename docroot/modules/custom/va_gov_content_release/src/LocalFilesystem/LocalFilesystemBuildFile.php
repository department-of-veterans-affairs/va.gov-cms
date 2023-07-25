<?php

namespace Drupal\va_gov_content_release\LocalFilesystem;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\va_gov_content_release\Exception\StrategyErrorException;

/**
 * Local filesystem build file service.
 *
 * This service is used to submit a content release request in the form of a
 * build file to the local filesystem.
 */
class LocalFilesystemBuildFile implements LocalFilesystemBuildFileInterface {

  const FILE_CONTENTS = 'build plz';

  const FILE_URI = 'public://.buildrequest';

  /**
   * Filesystem service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $filesystem;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $filesystem
   *   The filesystem service.
   */
  public function __construct(FileSystemInterface $filesystem) {
    $this->filesystem = $filesystem;
  }

  /**
   * {@inheritDoc}
   */
  public function submit() : void {
    try {
      // The existence of this file triggers a content release.
      // See scripts/queue_runner/queue_runner.sh.
      $this->filesystem->saveData(static::FILE_CONTENTS, static::FILE_URI, FileSystemInterface::EXISTS_REPLACE);
    }
    catch (FileException $exception) {
      throw new StrategyErrorException('A content release request has failed with a filesystem exception.', $exception->getCode(), $exception);
    }
  }

}
