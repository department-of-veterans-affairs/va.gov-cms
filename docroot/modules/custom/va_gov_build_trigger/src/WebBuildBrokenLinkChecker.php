<?php

namespace Drupal\va_gov_build_trigger;

use Drupal\blazy\Utility\BlazyMarkdown;
use Drupal\Component\Serialization\Json;

/**
 * Handle the web Broken Link checker.
 */
class WebBuildBrokenLinkChecker {

  /**
   * Broken link path.
   */
  public const BROKEN_LINK_SUFFIX = '/docroot/vendor/va-gov/content-build/logs/vagovdev-broken-links.json';

  /**
   * The number of broken Links.
   *
   * @var int
   */
  protected $brokenLinksCount;

  /**
   * The summary of links.
   *
   * This format is controlled by the broken links script in the
   * front end build.  The content is in markdown format.
   *
   * @var string
   */
  protected $summary;

  /**
   * Load Broken Links.
   *
   * * @param string $appRoot
   *   The path to the application root.
   */
  public function loadBrokenLinks(string $appRoot) : void {
    $path = $this->getBrokenLinkPath($appRoot);

    if (!file_exists($path)) {
      return;
    }

    $contents = file_get_contents($path);
    $json = Json::decode($contents);

    $this->brokenLinksCount = $json['brokenLinksCount'] ?? 0;
    $this->summary = $json['summary'] ?? '';
  }

  /**
   * Get the path to the broken link build file.
   *
   * @param string $appRoot
   *   The path to the application root.
   *
   * @return string
   *   The path to the broken link file.
   */
  public function getBrokenLinkPath(string $appRoot) : string {
    return $appRoot . static::BROKEN_LINK_SUFFIX;
  }

  /**
   * Get the raw markdown broken link summary.
   *
   * @return string
   *   The markdown of the broken link summary.
   */
  public function getBrokenLinkSummary() : string {
    return $this->summary ?? '';
  }

  /**
   * Get Formatted Broken link Report.
   *
   * @return string
   *   A formatted Broken Link report.
   */
  public function getBrokenLinkFormattedReport() : string {
    return BlazyMarkdown::parse($this->getBrokenLinkSummary());
  }

  /**
   * Get the number of broken links.
   *
   * @return int
   *   The number of broken links.
   */
  public function getBrokenLinkCount() : int {
    return $this->brokenLinksCount ?? 0;
  }

}
