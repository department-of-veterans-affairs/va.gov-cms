<?php

namespace Drupal\va_gov_content_export\Archive;

use Drupal\Core\Site\Settings;

/**
 * Factory for ArchiveDirectory.
 */
class ArchiveArgsFactory {

  public const CONTENT_EXPORT = 'content';
  public const ASSET_EXPORT = 'asset';

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
    'cms-export-files',
    'php',
    'cms-content-export-latest.tar',
    'cms-asset-export-latest.tar',
  ];

  /**
   * Create ArchiveArgs for a content export.
   *
   * @return \Drupal\va_gov_content_export\Archive\ArchiveArgs
   *   The ArchiveArgs object.
   */
  public function createContentArgs() : ArchiveArgs {
    return new ArchiveArgs(
      $this->getExcludeList(),
      $this->getContentArchivePath(),
      'public://',
      $this->getContentArchiveFileName()
    );
  }

  /**
   * Create ArchiveArgs for a asset export.
   *
   * @return \Drupal\va_gov_content_export\Archive\ArchiveArgs
   *   The ArchiveArgs object.
   */
  public function createAssetArgs() : ArchiveArgs {
    $excludes = $this->getExcludeList();
    // Exclude the directory where tome sync content goes.
    $excludes[] = 'cms-export-content';

    return new ArchiveArgs(
      $excludes,
      $this->getAssetArchivePath(),
      'public://',
      $this->getAssetArchiveFileName()
    );
  }

  /**
   * Get the name of the tar file for the content archive.
   *
   * @return string
   *   The uri of the file.
   */
  protected function getContentArchiveFileName() : string {
    return Settings::get('va_gov_content_export_content_archive_file_name', 'public://cms-content-export-latest.tar');
  }

  /**
   * Get the Name of the content archive directory relative to the CWD.
   *
   * @return string
   *   The uri of the directory to tar.
   */
  protected function getContentArchivePath() : string {
    return Settings::get('va_gov_content_export_content_archive_directory', 'cms-export-content');

  }

  /**
   * Get the name of the tar file for the asset archive.
   *
   * @return string
   *   The uri of the file.
   */
  protected function getAssetArchiveFileName() : string {
    return Settings::get('va_gov_content_export_content_archive_file_name', 'public://cms-asset-export-latest.tar');
  }

  /**
   * Get the Name of the Asset archive directory relative to the CWD.
   *
   * @return string
   *   The uri of the directory to tar.
   */
  protected function getAssetArchivePath() : string {
    return Settings::get('va_gov_content_export_asset_archive_directory', '.');
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
