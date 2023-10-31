<?php

namespace Drupal\va_gov_content_release\Status;

/**
 * An interface for the Content Release Status service.
 *
 * This service provides information about the status of the content release.
 */
interface StatusInterface {

  // The following content release strategy plugins provide additional details
  // about the build because they allow selection of versions, keep a build
  // log, etc.
  const ADDITIONAL_BUILD_DETAILS_STRATEGY_IDS = [
    'local_filesystem_build_file',
  ];

  /**
   * Get the current release state, as provided by the Release State Manager.
   *
   * @return string
   *   The current release state.
   */
  public function getCurrentReleaseState() : string;

  /**
   * Get a human-readable version of the current release state.
   *
   * @return string
   *   The current release state, in human-readable form.
   */
  public function getHumanReadableCurrentReleaseState() : string;

  /**
   * Get the last successful release timestamp.
   *
   * @return int
   *   The last successful release date, as a Unix timestamp.
   */
  public function getLastReleaseCompleteTimestamp() : int;

  /**
   * Get the last successful release date, formatted for display.
   *
   * @return string
   *   The last successful release date, formatted for display.
   */
  public function getLastReleaseCompleteDate() : string;

  /**
   * Whether there are additional build details worth sharing.
   *
   * For instance:
   * - content-build version
   * - vets-website version
   * - next-build version
   * - build log.
   *
   * @return bool
   *   Whether there are additional build details worth sharing.
   */
  public function hasAdditionalBuildDetails() : bool;

  /**
   * Get the content-build version.
   *
   * @return string
   *   The content-build version.
   */
  public function getContentBuildVersion() : string;

  /**
   * Get the vets-website version.
   *
   * @return string
   *   The vets-website version.
   */
  public function getVetsWebsiteVersion() : string;

  /**
   * Get the build log path.
   *
   * This is relative to the docroot, but starts with a slash.
   *
   * @return string
   *   The build log path.
   */
  public function getBuildLogPath() : string;

}
