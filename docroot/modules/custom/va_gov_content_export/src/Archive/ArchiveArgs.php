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
   * @string
   */
  protected $currentWorkingDirectory;

  /**
   * The name of the output tar file.
   *
   * The location and name of the output file relative to the base path.
   * Can be a uri.
   *
   * @string
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
   *   The base path to archive.
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
   * @return array
   */
  public function getExcludes(): array {
    return $this->excludes;
  }

  /**
   * @param array $excludes
   *
   * @return ArchiveArgs
   */
  public function setExcludes(array $excludes): ArchiveArgs {
    $this->excludes = $excludes;
    return $this;
  }

  /**
   * @return string
   */
  public function getArchiveDirectory(): string {
    return $this->archiveDirectory;
  }

  /**
   * @param string $archiveDirectory
   *
   * @return ArchiveArgs
   */
  public function setArchiveDirectory(string $archiveDirectory): ArchiveArgs {
    $this->archiveDirectory = $archiveDirectory;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getCurrentWorkingDirectory() {
    return $this->currentWorkingDirectory;
  }

  /**
   * @param mixed $currentWorkingDirectory
   *
   * @return ArchiveArgs
   */
  public function setCurrentWorkingDirectory($currentWorkingDirectory) {
    $this->currentWorkingDirectory = $currentWorkingDirectory;
    return $this;
  }

  /**
   * @return string
   */
  public function getOutputPath(): string {
    return $this->outputPath;
  }

  /**
   * @param string $outputPath
   *
   * @return ArchiveArgs
   */
  public function setOutputPath(string $outputPath): ArchiveArgs {
    $this->outputPath = $outputPath;
    return $this;
  }

}
