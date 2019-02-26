<?php

namespace Drupal\va_gov_migrate\Plugin\migrate\source;

use Drupal\migration_tools\Message;
use Drupal\migration_tools\Plugin\migrate\source\UrlList;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Drupal\migrate\Plugin\MigrationInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Gets frontmatter and page urls from metalsmith files.
 *
 * This source requires a list of urls that are either metalsmith files
 * or a github directory listing of metalsmith files.
 *
 * @MigrateSource(
 *  id = "metalsmith_source"
 * )
 */
class MetalsmithSource extends UrlList {
  /**
   * Array of assoc arrays of frontmatter data and website url.
   *
   * @var array
   */
  protected $rows;

  /**
   * Name of the template file to filter for in this migration.
   *
   * @var array
   */
  protected $templates;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);

    $this->templates = empty($configuration['templates']) ? '' : $configuration['templates'];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\migrate\MigrateException
   * @throws \Drupal\migrate\MigrateSkipRowException
   */
  public function initializeIterator() {
    $this->rows = [];

    foreach ($this->urls as $url) {
      // If it's a markdown file, process it.
      if (substr($url, -3) == '.md') {
        $this->addRow($url);
      }
      // Otherwise, assume it's a directory listing.
      else {
        $this->crawlDirectory($url);

      }
    }

    $this->validateRows();
    return new \ArrayIterator($this->rows);
  }

  /**
   * Removes any rows with duplicate key fields (urls).
   */
  protected function validateRows() {
    $unique_rows = [];
    $urls = [];
    foreach ($this->rows as $row) {
      if (in_array($row['url'], $urls)) {
        $key = array_search($row['url'], array_column($this->rows, 'url'));
        $this->rows[$key] = array_merge($row, $this->rows[$key]);
        Message::make('Found duplicate entry for @url', ['@url' => $row['url']], Message::DEBUG);
      }
      else {
        $unique_rows[] = $row;
        $urls[] = $row['url'];
      }
    }

    $this->rows = $unique_rows;
  }

  /**
   * Read github directory recursively to find and parse all .md files in it.
   *
   * @param string $url
   *   The url of the github directory.
   *
   * @throws \Drupal\migrate\MigrateException
   * @throws \Drupal\migrate\MigrateSkipRowException
   */
  protected function crawlDirectory($url) {
    // Read the page at the url.
    if (!($html = self::readUrl($url))) {
      return;
    }

    // Find all the links in the "files" table.
    $query_path = htmlqp($html);
    $links = $query_path->find('table.files a');

    /** @var \QueryPath\DOMQuery $link */
    foreach ($links as $link) {
      $path = $link->attr('href');
      // File and directory paths are always relative.
      $link_href = 'https://github.com' . $path;

      // If it's a markdown file, process it.
      if (substr($path, -3) == '.md') {
        $this->addRow($link_href);
      }
      // If it's a child directory, recurse into it.
      elseif ($link->closest('td')->prev()->find('svg')->attr('aria-label') == 'directory') {
        $current_path = parse_url($url, PHP_URL_PATH);
        $link_path = parse_url($link_href, PHP_URL_PATH);
        if (strpos($link_path, $current_path) === 0 && $link_path != $current_path) {
          $this->crawlDirectory($link_href);
        }
      }
    }
  }

  /**
   * Add a row with frontmatter and the url of the associated web page.
   *
   * @param string $url
   *   The url of the markdown file to get fields from.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  protected function addRow($url) {
    if (!($row = self::readMetalsmithFile($url))) {
      return;
    }

    // Migrate metalsmith only and no fully react pages.
    if (empty($row['layout']) || 'page-react.html' == $row['layout']) {
      return;
    }

    // Filter for 'templates' field defined in migration yml, if any.
    if (!empty($this->templates) && !in_array($row['template'], $this->templates)) {
      return;
    }

    self::setPagePath($url, $row);
    if (empty($row['url'])) {
      return;
    }

    // Extract the plainlanguage date, if any.
    if (!empty($row['plainlanguage']) && preg_match('/0?(\d+)[-\.]0?(\d+)[-\.](\d+)/', $row['plainlanguage'], $matches)) {
      $row['plainlanguage_date'] = $matches[1] . '/' . $matches[2] . '/' . $matches[3];
    }

    $this->rows[] = $row;
  }

  /**
   * Parse Metalsmith file and return an array of front matter values.
   *
   * @param string $url
   *   The file's url (used for error message).
   * @param string $page_content
   *   Gets populated with the page content section of the file (optional)
   *
   * @return array
   *   The front matter values, keyed on field names.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public static function readMetalsmithFile($url, &$page_content = NULL) {
    if (!($markdown = self::readRawFile($url))) {
      return [];
    }

    /*
     * Metalsmith source pages look like this:
     *  ---
     *  [front matter in yaml format]
     *  ---
     *  [page content in markup and html]
     */

    // Add asterisks to markdown line breaks so we don't explode on them.
    $markdown = str_replace('------', '**--*--*--**', $markdown);
    $page_part = explode('---', $markdown);

    if (count($page_part) < 2) {
      Message::make('No front matter in @url', ['@url' => parse_url($url, PHP_URL_PATH)], Message::DEBUG);
      return [];
    }
    else {
      if ($page_content !== NULL && count($page_part) > 2) {
        $page_content = str_replace('**--*--*--**', '------', $page_part[2]);
      }

      try {
        return Yaml::parse($page_part[1]);
      }
      catch (ParseException $exception) {
        Message::make('Unable to parse the YAML string: @message', ['@message' => $exception->getMessage()], Message::ERROR);
        return [];
      }
    }
  }

  /**
   * Read the page at the url.
   *
   * @param string $url
   *   The url of the page to read.
   *
   * @return bool|string
   *   The contents of the page or FALSE on failure.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public static function readUrl($url) {
    $httpClient = \Drupal::httpClient();

    try {
      $response = $httpClient->get($url);
      if (empty($response)) {
        Message::make('No response at @url.', ['@url' => $url], Message::ERROR);
        return FALSE;
      }
    }
    catch (RequestException $e) {
      Message::make('Error message: @message at @url.', [
        '@message' => $e->getMessage(),
        '@url' => $url,
      ], Message::ERROR);
      return FALSE;
    }

    return $response->getBody()->getContents();
  }

  /**
   * Read the raw contents of the referenced file.
   *
   * @param string $url
   *   The github url of the file.
   *
   * @return bool|string
   *   The contents of the file, or FALSE if the read failed.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public static function readRawFile($url) {
    // Get the path to the raw file.
    $url_path = parse_url($url, PHP_URL_PATH);
    $url_path = str_replace('/blob', '', $url_path);
    $url = 'https://raw.githubusercontent.com' . $url_path;

    // Read the file.
    if (!($contents = self::readUrl($url))) {
      Message::make('Couldn\'t read the file at @url', ['@url' => $url], Message::ERROR);
      return FALSE;
    }

    return $contents;
  }

  /**
   * Set the 'url' key to the va.gov path that corresponds to a metalsmith file.
   *
   * Also set the 'path' key, used for drupal node alias.
   *
   * @param string $url
   *   The url of the metalsmith file.
   * @param array $row
   *   The row to set the url on.
   */
  public static function setPagePath($url, array &$row) {
    $url_path = parse_url($url, PHP_URL_PATH);
    // Get the path without the file name for index pages.
    if (preg_match('/\/department-of-veterans-affairs\/vagov-content\/blob\/master\/pages\/([^\.]+)\/index\.md/', $url_path, $matches)) {
      $path = $matches[1];
    }
    // Get the path with the file name for all others.
    elseif (preg_match('/\/department-of-veterans-affairs\/vagov-content\/blob\/master\/pages\/([^\.]+)\.md/', $url_path, $matches)) {
      $path = $matches[1];
    }
    if (!empty($path)) {
      $row['url'] = 'https://www.va.gov/' . $path . '/';
      $row['path'] = '/' . $path;
    }

  }

}
