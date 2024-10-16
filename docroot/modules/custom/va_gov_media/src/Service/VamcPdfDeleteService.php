<?php

namespace Drupal\va_gov_media\Service;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\File\Exception\FileNotExistsException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\entity_usage_addons\Service\Usage;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;
use Drupal\s3fs\S3fsFileSystemD103;
use Drupal\user\UserDataInterface;

/**
 * Service to cycle through and remove PDFs that are not attached to content.
 */
class VamcPdfDeleteService implements VamcPdfDeleteInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   *  The entity manager.
   */
  private $entityTypeManager;

  /**
   * The usage service.
   *
   * @var \Drupal\entity_usage_addons\Service\Usage
   *   The usage service.
   */
  protected $usage;

  /**
   * The stream wrapper manager service.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManager
   *   The stream wrapper manager service.
   */
  protected $streamWrapperManager;

  /**
   * Drupal\Core\Datetime\DateFormatter definition.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The S3fs service.
   *
   * @var \Drupal\s3fs\S3fsFileSystemD103
   */
  protected $s3fs;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The user data service.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $user;

  /**
   * Construct the PDF Delete Service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity manager.
   * @param \Drupal\entity_usage_addons\Service\Usage $usage
   *   The usage service.
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManager $stream_wrapper_manager
   *   The stream wrapper manager service.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   * @param \Drupal\s3fs\S3fsFileSystemD103 $s3fsfileservice
   *   The S3fs service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   * @param \Drupal\user\UserDataInterface $user
   *   The user data service.
   */
  public function __construct(
    EntityTypeManager $entity_type_manager,
    Usage $usage,
    StreamWrapperManager $stream_wrapper_manager,
    DateFormatter $date_formatter,
    S3fsFileSystemD103 $s3fsfileservice,
    LoggerChannelFactoryInterface $loggerFactory,
    UserDataInterface $user,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->usage = $usage;
    $this->streamWrapperManager = $stream_wrapper_manager;
    $this->dateFormatter = $date_formatter;
    $this->s3fs = $s3fsfileservice;
    $this->loggerFactory = $loggerFactory;
    $this->user = $user;
  }

  /**
   * Find and delete VAMC PDFs that are not attached to content.
   */
  public function vamcPdfDelete(): void {
    $vha_total = 0;
    $non_vha_total = 0;
    $logger = $this->loggerFactory->get('va_gov_media');
    $media_storage = $this->entityTypeManager->getStorage('media');
    $pdf_list = [];
    $deleted_pdfs = [
      ['File Name', 'Section', 'Upload Date', 'Uploaded By'],
    ];
    $query = $media_storage->getQuery();
    $pdf_list = $query
      ->accessCheck(FALSE)
      ->condition('bundle', 'document')
      ->execute();

    $total_pdfs = count($pdf_list);
    // Used for name csv and directory.
    $timestamp = date('Y-m-d-H:i:s');

    foreach ($pdf_list as $pdf_id) {
      $is_used = $this->usage->getUsageTotal('media', $pdf_id);
      /** @var \Drupal\media\MediaInterface $pdf */
      $pdf = $media_storage->load($pdf_id);
      $pdf_name = $pdf->get('name')->value;
      if (!$pdf->hasField('field_document')) {
        $logger->warning("PDF {$pdf_id} not deleted because it has no document field.");
        continue;
      }
      if ($is_used === 0 && $pdf->hasField('field_document')) {
        $section_id = $pdf->get('field_owner')->getValue()[0]['target_id'];
        if (!$section_id) {
          $logger->warning("PDF {$pdf_id} has no section id.");
          continue;
        }
        $section = $this->entityTypeManager->getStorage('taxonomy_term')->load($section_id);
        $parents = $this->entityTypeManager->getStorage('taxonomy_term')->loadAllParents($section_id);
        $parents = array_keys($parents);
        $section_name = $section ? $section->get('name')->value : 'No Section';
        // 8 is the Term ID for VAMC Facilities.
        if (in_array('8', $parents)) {
          $user_id = $pdf->get('uid')->getValue()[0]['target_id'];
          $user = $this->entityTypeManager->getStorage('user')->load($user_id);
          $username = $user ? $user->getAccountName() : 'Unknown';
          $created_time = $this->dateFormatter->format($pdf->get('created')->value, 'short');
          $deleted_pdfs[] = [
            $pdf_name,
            $section_name,
            $created_time,
            $username,
          ];
          $this->deletePdf($pdf, $timestamp);
          $vha_total++;

        }
        else {
          $section_error = $section ? $section->get('name')->value : $section_id;
          $logger->info("PDF ({$pdf_name}) not deleted because it is not in a VHA section (Name or ID: {$section_error}).");
          $non_vha_total++;
        }
      }
    }
    $this->writeCsv($deleted_pdfs, $timestamp);
    $logger->info('PDFs deleted and CSV written.');
    $logger->info("Total PDFs: {$total_pdfs}");
    $logger->info("VHA unattached PDFs deleted: {$vha_total}");
    $logger->info("Non-VHA unattached PDFs not deleted: {$non_vha_total}");
  }

  /**
   * Delete PDF.
   *
   * @param \Drupal\media\MediaInterface $pdf_entity
   *   The PDF entity to delete.
   * @param string $timestamp
   *   The timestamp to use for the directory.
   *
   * @return void
   *   Return void.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function deletePdf(MediaInterface $pdf_entity, string $timestamp): void {
    $file = $pdf_entity->field_document->entity;
    if (!$file instanceof FileInterface) {
      $pdf_name = $pdf_entity->get('name')->value;
      $this->loggerFactory->get('va_gov_media')->warning("PDF {$pdf_name} does not have a file attached to it. No file was copied. Deleting the media entity.");
      $pdf_entity->delete();
      return;
    }
    $uri = $file->getFileUri();
    $absolute_path = $this->s3fs->realpath($uri);
    $path = 's3://' . $timestamp;
    $this->s3fs->mkdir($path, 0755, 0);
    $destination = $path . '/' . $file->getFilename();
    try {
      $this->s3fs->copy($absolute_path, $destination);
    }
    catch (FileNotExistsException $e) {
      $this->loggerFactory->get('va_gov_media')->error('Error copying file to S3: @error', ['@error' => $e->getMessage()]);
      return;
    }
    $file->delete();
    $pdf_entity->delete();
  }

  /**
   * Write CSV.
   *
   * @param array $deleted_pdfs
   *   The list of deleted PDFs.
   * @param string $timestamp
   *   The timestamp to use for the directory.
   *
   * @return void
   *   Return void.
   */
  protected function writeCsv(array $deleted_pdfs, string $timestamp): void {
    $filename = DRUPAL_ROOT . '/sites/default/files/' . $timestamp . '-deleted-pdf-list.csv';

    try {
      // Open a file in write mode ('w')
      $file = fopen($filename, 'w+');
    }
    catch (\Exception $e) {
      $this->loggerFactory->get('va_gov_media')->error('Error opening or creating CSV file: @error', ['@error' => $e->getMessage()]);
      return;
    }

    // Loop through file pointer and a line.
    foreach ($deleted_pdfs as $fields) {
      fputcsv($file, $fields);
    }
    fclose($file);
    $destination = 's3://' . $timestamp . '/' . $timestamp . '-deleted-pdf-list.csv';
    $this->s3fs->copy($filename, $destination);
    unlink($filename);
    $this->loggerFactory->get('va_gov_media')->info("CSV written to {$destination}");
  }

}
