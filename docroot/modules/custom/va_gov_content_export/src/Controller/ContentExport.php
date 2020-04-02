<?php

namespace Drupal\va_gov_content_export\Controller;

use Alchemy\Zippy\Archive\ArchiveInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\Site\Settings;
use Drupal\va_gov_content_export\ArchiveDirectory;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Page Controller for Content Export.
 */
class ContentExport extends ControllerBase {

  /**
   * A default list of files to exclude.
   *
   * @var string[]
   */
  protected static $defaultExcludeList = [
    '.htaccess',
    'css',
    'js',
    'xmlsitemap',
    'cms-export-content',
    'cms-export-files',
    'php',
  ];

  /**
   * The Archiver.
   *
   * @var \Drupal\va_gov_content_export\ArchiveDirectory
   */
  protected $archiver;

  /**
   * ContentExport constructor.
   *
   * @param \Drupal\va_gov_content_export\ArchiveDirectory $archiver
   *   The Archiver!
   */
  public function __construct(ArchiveDirectory $archiver) {
    $this->archiver = $archiver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov.content_export.archive_directory')
    );
  }

  /**
   * Redirect to the tar file.
   *
   * The end point follows the following process:
   * 1. Get the directory to tar
   * 2. Run tar update on the hash file
   * 3. Redirect to to the file.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Response.
   */
  public function redirectToFile() : Response {
    try {
      $this->archive();
      $file_name = $this->getArchiveFileName();
      if (!file_exists($file_name)) {
        throw new FileException("Tar file does not exist at $file_name");
      }
      $url = file_create_url($file_name);
      return RedirectResponse::create($url);
    }
    catch (Exception $e) {
      watchdog_exception('VA-EXPORT', $e);
      return Response::create('Error creating tar file', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  /**
   * Tar up a directory.
   *
   * @return string
   *   The path to the tar.
   */
  protected function archive() : ArchiveInterface {
    return $this->archiver->archive(
      $this->getDirectoryToArchive(),
      $this->getArchiveFileName(),
      $this->getExcludeList()
    );
  }

  /**
   * Get the export directory.
   *
   * @return string
   *   The uri of the directory to export.
   */
  protected function getArchiveFileName() : string {
    return Settings::get('va_gov_content_export_archive_file_name', 'public://cms-content-export-latest.tar');
  }

  /**
   * Get the directory to tar.
   *
   * @return string
   *   The directory to archive.
   */
  protected function getDirectoryToArchive() : string {
    return Settings::get('va_gov_content_export_directory', 'public://');
  }

  /**
   * Get a list of file names to exclude.
   *
   * @return array
   *   An array of string files names to exclude
   */
  protected function getExcludeList() : array {
    return Settings::get('va_gov_content_export_files_to_ignore', []) ?:
      static::$defaultExcludeList;
  }

}
