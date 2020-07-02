<?php

namespace Drupal\va_gov_content_export\Archive;

use Alchemy\Zippy\Archive\ArchiveInterface;
use Alchemy\Zippy\Zippy;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\File\FileSystemInterface;
use Drupal\va_gov_content_export\Event\ContentExportPreTarEvent;

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
   * A Drupal event dispatcher object.
   *
   * @var \Drupal\Core\EventDispatcher
   */
  protected $eventDispatcher;

  /**
   * A Drupal file system object.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * ArchiveDirectory constructor.
   *
   * @param \Alchemy\Zippy\Zippy $zippy
   *   Zippy.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   Drupal FileSystem.
   * @param \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $eventDispatcher
   *   Drupal event dispatcher.
   */
  public function __construct(Zippy $zippy, FileSystemInterface $fileSystem, ContainerAwareEventDispatcher $eventDispatcher) {
    $this->zippy = $zippy;
    $this->fileSystem = $fileSystem;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * Archive a Directory.
   *
   * @param \Drupal\va_gov_content_export\Archive\ArchiveArgs $archiveArgs
   *   The arguments to use to create the archive.
   *
   * @return \Alchemy\Zippy\Archive\ArchiveInterface
   *   The Archive which was created.
   */
  public function archive(ArchiveArgs $archiveArgs) : ArchiveInterface {
    // @TODO Add locking/queueing/waiting so only one archive is occurring at a time.
    $output_dir = dirname($archiveArgs->getOutputPath());
    $this->fileSystem->prepareDirectory($output_dir, FileSystemInterface::CREATE_DIRECTORY);

    $input_path = $this->fileSystem->realpath($archiveArgs->getCurrentWorkingDirectory());
    $files = [
      'exclude' => $archiveArgs->getExcludes(),
      'path' => $archiveArgs->getArchiveDirectory(),
      'cwd' => $input_path,
    ];
    $real_path = $this->fileSystem->realpath($archiveArgs->getOutputPath());

    // Deleting the file before it's created improves performance.
    $this->fileSystem->delete($archiveArgs->getOutputPath());
    // Dispatch the Pre Tar event so that subscribers can use it.
    $this->eventDispatcher->dispatch(ContentExportPreTarEvent::CONTENT_EXPORT_PRE_TAR_EVENT, new ContentExportPreTarEvent("{$archiveArgs->getCurrentWorkingDirectory()}{$archiveArgs->getArchiveDirectory()}", $this->fileSystem));

    return $this->zippy->create($real_path, $files, TRUE);
  }

}
