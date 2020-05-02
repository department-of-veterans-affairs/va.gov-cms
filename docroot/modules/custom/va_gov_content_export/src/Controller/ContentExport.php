<?php

namespace Drupal\va_gov_content_export\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\va_gov_content_export\Archive\ArchiveArgs;
use Drupal\va_gov_content_export\Archive\ArchiveArgsFactory;
use Drupal\va_gov_content_export\Archive\ArchiveDirectory;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Page Controller for Content Export.
 */
class ContentExport extends ControllerBase {

  /**
   * The Archiver.
   *
   * @var \Drupal\va_gov_content_export\Archive\ArchiveDirectory
   */
  protected $archiver;

  /**
   * Kill switch so that Drupal won't serve a cached response for the archive request. 
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * Archive Args Factory.
   *
   * @var \Drupal\va_gov_content_export\Archive\ArchiveArgsFactory
   */
  private $archiveArgsFactory;

  /**
   * The allowed export types.
   *
   * @var string[]
   */
  public const ALLOWED_EXPORT_TYPES = ['content', 'asset'];

  /**
   * ContentExport constructor.
   *
   * @param \Drupal\va_gov_content_export\Archive\ArchiveDirectory $archiver
   *   The Archiver!
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $killSwitch
   *   Kill switch.
   * @param \Drupal\va_gov_content_export\Archive\ArchiveArgsFactory $archiveArgsFactory
   *   The Archive Args factory.
   */
  public function __construct(ArchiveDirectory $archiver, KillSwitch $killSwitch, ArchiveArgsFactory $archiveArgsFactory) {
    $this->archiver = $archiver;
    $this->killSwitch = $killSwitch;
    $this->archiveArgsFactory = $archiveArgsFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov.content_export.archive_directory'),
      $container->get('page_cache_kill_switch'),
      $container->get('va_gov.content_export.archive_args_factory')
    );
  }

  /**
   * Redirect to the tar file.
   *
   * The end point follows the following process:
   * 1. Get the directory to tar
   * 2. Run tar --create on the archive directory
   * 3. Redirect to to the file.
   *
   * @param string $export_type
   *   The type of export to tar.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Response.
   */
  public function redirectToFile(string $export_type) : Response {
    if (!$export_type || !in_array($export_type, static::ALLOWED_EXPORT_TYPES)) {
      throw new NotFoundHttpException();
    }

    // Disable anon page cache for this response.
    $this->killSwitch->trigger();
    try {
      $archive_args = $this->getArchiveArgs($export_type);
      $this->archiver->archive($archive_args);
      $file_name = $archive_args->getOutputPath();

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
   * Build the Archive Args for the export type.
   *
   * @param string $export_type
   *   The type of export.  This can be content or asset.
   *
   * @return \Drupal\va_gov_content_export\Archive\ArchiveArgs
   *   The ArchiveArgs object.
   *
   * @throws \Exception
   */
  protected function getArchiveArgs(string $export_type) : ArchiveArgs {
    switch ($export_type) {
      case ArchiveArgsFactory::ASSET_EXPORT:
        return $this->archiveArgsFactory->createAssetArgs();

      case ArchiveArgsFactory::CONTENT_EXPORT:
        return $this->archiveArgsFactory->createContentArgs();
    }

    throw new Exception('Export Type ' . $export_type . ' does not exist');
  }

}
