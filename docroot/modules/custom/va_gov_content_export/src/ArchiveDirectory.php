<?php

namespace Drupal\va_gov_content_export;

use Alchemy\Zippy\Archive\ArchiveInterface;
use Alchemy\Zippy\Zippy;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Psr\Log\LoggerInterface;

/**
 * Archive a file path for CMS Content Export.
 */
class ArchiveDirectory {

  /**
   * The Zippy Class used to create archives.
   *
   * @var \Alchemy\Zippy\Zippy
   */
  protected $zippy;

  /**
   * A Drupal file system object.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Logger Manager.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * ArchiveDirectory constructor.
   *
   * @param \Alchemy\Zippy\Zippy $zippy
   *   Zippy.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   Drupal FileSystem.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger Manger.
   */
  public function __construct(Zippy $zippy, FileSystemInterface $fileSystem, LoggerInterface $logger) {
    $this->zippy = $zippy;
    $this->fileSystem = $fileSystem;
    $this->logger = $logger;
  }

  /**
   * Archive a Directory.
   *
   * @param string $input_dir
   *   The input path or uri to the directory to tar.
   * @param string $output_path
   *   The name of the output tar file.
   * @param array $file_to_exclude
   *   An array of file and directory names to exclude.
   *
   * @return \Alchemy\Zippy\Archive\ArchiveInterface
   *   The Archive which was created.
   */
  public function archive(string $input_dir, string $output_path, array $file_to_exclude = []) : ArchiveInterface {
    // @TODO Add locking/queueing/waiting so only one archive is occurring at a time.
    $output_dir = dirname($output_path);
    $this->fileSystem->prepareDirectory($output_dir, FileSystemInterface::CREATE_DIRECTORY);

    $input_path = $this->fileSystem->realpath($input_dir);
    $files = [
      'exclude' => $file_to_exclude,
      'path' => '.',
      'cwd' => $input_path,
    ];
    $real_path = $this->fileSystem->realpath($output_path);
    return $this->zippy->create($real_path, $files, TRUE);
  }

  /**
   * Get a list of the files to tar.
   *
   * @param string $input_dir
   *   The directory to tar.
   * @param string[] $file_to_exclude
   *   An array of files to exclude.
   *
   * @return array
   *   An array of files keyed by url.  See FileSystemInterface::scanDirectory
   */
  protected function getFilesList(string $input_dir, array $file_to_exclude = []) : array {
    try {
      $options = [
        'nomask' => $file_to_exclude,
      ];
      return $this->fileSystem->scanDirectory($input_dir, '/.*/', $options);
    }
    catch (FileException $e) {
      $this->logger->error('Content Export Tar file was not created.  See exception output for more information.');
      watchdog_exception('VA-EXPORT', $e);
    }
  }

}
