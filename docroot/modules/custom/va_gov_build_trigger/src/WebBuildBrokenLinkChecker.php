<?php

namespace Drupal\va_gov_build_trigger;

use Drupal\blazy\Utility\BlazyMarkdown;
use Drupal\Component\Serialization\Json;

class WebBuildBrokenLinkChecker {
  public CONST BROKEN_LINK_SUFFIX = '/docroot/vendor/va-gov/web/logs/vagovdev-broken-links.json';

  /**
   * Web Build Command Builder class.
   *
   * @var \Drupal\va_gov_build_trigger\WebBuildCommandBuilder
   */
  protected $webCommandBuilder;

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
   * WebBuildBrokenLinkChecker constructor.
   *
   * @param \Drupal\va_gov_build_trigger\WebBuildCommandBuilder $webCommandBuilder
   *   The web command builder class.
   */
  public function __construct(WebBuildCommandBuilder $webCommandBuilder) {
    $this->webCommandBuilder = $webCommandBuilder;
  }


  /**
   * Load Broken Links.
   */
  public function loadBrokenLinks() : void {
    $path = $this->$this->getBrokenLinkPath();
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
   * @return string
   *   The path to the broken link file.
   */
  public function getBrokenLinkPath() : string {
    return $this->webCommandBuilder->getAppRoot() . static::BROKEN_LINK_SUFFIX;
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
