<?php

namespace Drupal\va_gov_content_export\Archive;

/**
 * A value object storing arguments to archive.
 */
class ArchiveArgs {

  /**
   * An array of file and directory names to exclude.
   *
   * @var array
   */
  protected $excludes;

  /**
   * Path to archive relative to the base path.
   *
   * The path to archive relative to the base path.
   * Can be a uri.
   *
   * @var string
   */
  protected $archiveDirectory;

  /**
   * The input path or uri to the directory to tar.
   *
   * The tar command will cd to this directory before making a call.
   * Can be a uri.
   *
   * @var string
   */
  protected $currentWorkingDirectory;

  /**
   * The name of the output tar file.
   *
   * The location and name of the output file relative to the base path.
   * Can be a uri.
   *
   * @var string
   */
  protected $outputPath;

  /**
   * ArchiveArg constructor.
   *
   * @param array $exclude
   *   Array of files to exclude.
   * @param string $archive_path
   *   The Relative Archive Path.
   * @param string $basePath
   *   The base path to archive, current working directory.
   * @param string $output_path
   *   The output path.
   */
  public function __construct(array $exclude, string $archive_path, string $basePath, string $output_path) {
    $this->excludes = $exclude;
    $this->archiveDirectory = $archive_path;
    $this->currentWorkingDirectory = $basePath;
    $this->outputPath = $output_path;
  }

  /**
   * Get Excluded Files.
   *
   * @return array
   *   Array of excluded files.
   */
  public function getExcludes(): array {
    return $this->excludes;
  }

  /**
   * Get Directory to Archive.
   *
   * @return string
   *   Path to archive.
   */
  public function getArchiveDirectory(): string {
    return $this->archiveDirectory;
  }

  /**
   * Get the current working directory to use as the CWD for the tar command.
   *
   * @return string
   *   The CWD.
   */
  public function getCurrentWorkingDirectory() : string {
    return $this->currentWorkingDirectory;
  }

  /**
   * Get the Path of the final tar file.
   *
   * @return string
   *   Path to tar file.  Can be URI.
   */
  public function getOutputPath(): string {
    return $this->outputPath;
  }

}
