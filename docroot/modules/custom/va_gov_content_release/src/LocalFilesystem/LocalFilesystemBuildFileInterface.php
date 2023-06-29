<?php

namespace Drupal\va_gov_content_release\LocalFilesystem;

/**
 * An interface for the local filesystem build file service.
 *
 * This service is used to submit a content release request in the form of a
 * build file to the local filesystem.
 *
 * @see \Drupal\va_gov_content_release\LocalFilesystem\LocalFilesystemBuildFile
 */
interface LocalFilesystemBuildFileInterface {

  /**
   * Submit a local filesystem content release request.
   *
   * @throws \Drupal\va_gov_content_release\Exception\StrategyErrorException
   *   If the repository dispatch fails.
   */
  public function submit() : void;

}
