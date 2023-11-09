<?php

namespace Drupal\va_gov_content_release\FrontendVersionSearch;

use Drupal\va_gov_content_release\Frontend\FrontendInterface;

/**
 * An interface for the FrontendVersionSearch service.
 *
 * This service allows (in some environments) listing the available versions of
 * the frontend that is used to perform content releases.
 */
interface FrontendVersionSearchInterface {

  /**
   * Get branch and PR references matching the given search string.
   *
   * @param \Drupal\va_gov_content_release\Frontend\FrontendInterface $frontend
   *   The frontend whose versions we are requesting.
   * @param string $query
   *   Search string.
   * @param int $count
   *   Number of references to return.
   *
   * @return string[]
   *   An array of labeled git references.
   */
  public function getMatchingReferences(FrontendInterface $frontend, string $query, int $count) : array;

  /**
   * Return frontend branch names matching the given string.
   *
   * @param \Drupal\va_gov_content_release\Frontend\FrontendInterface $frontend
   *   The frontend whose versions we are requesting.
   * @param string $query
   *   Search string.
   *
   * @return string[]
   *   Array of branch names.
   */
  public function getMatchingBranches(FrontendInterface $frontend, string $query) : array;

  /**
   * Return frontend pull requests matching the given string.
   *
   * @param \Drupal\va_gov_content_release\Frontend\FrontendInterface $frontend
   *   The frontend whose versions we are requesting.
   * @param string $query
   *   Search string.
   *
   * @return string[]
   *   Array of Pull Request titles.
   */
  public function getMatchingPullRequests(FrontendInterface $frontend, string $query) : array;

}
