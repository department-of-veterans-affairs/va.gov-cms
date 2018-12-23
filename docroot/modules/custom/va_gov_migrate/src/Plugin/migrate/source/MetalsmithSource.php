<?php

namespace Drupal\va_gov_migrate\Plugin\migrate\source;

use Drupal\migration_tools\Message;
use Drupal\migration_tools\Plugin\migrate\source\UrlList;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Gets frontmatter and page urls from metalsmith files in github directories.
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
   * {@inheritdoc}
   *
   * @throws \Drupal\migrate\MigrateException
   * @throws \Drupal\migrate\MigrateSkipRowException
   */
  public function initializeIterator() {
    $this->rows = [];

    foreach ($this->urls as $url) {
      $this->getMarkdownData($url);
    }

    $this->validateRows();
    return new \ArrayIterator($this->rows);
  }

  /**
   * Removes any rows with duplicate key fields (url).
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
  protected function getMarkdownData($url) {
    // Read the page at the url.
    if (!($html = self::readUrl($url))) {
      return;
    }

    $query_path = htmlqp($html);
    $links = $query_path->find('a');

    foreach ($links as $link) {
      $path = $link->attr('href');
      $link_href = 'https://github.com' . $path;
      // If it's a markdown file, process it.
      if (substr($path, -3) == '.md') {
        $this->addRow($link_href);
      }
      else {
        // If it's a child directory, recurse into it.
        $current_path = parse_url($url, PHP_URL_PATH);
        $link_path = parse_url($link_href, PHP_URL_PATH);
        if (strpos($link_path, $current_path) === 0 && $link_path != $current_path) {
          $this->getMarkdownData($link_href);
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
    // Get the path to the raw markdown.
    $url_path = parse_url($url, PHP_URL_PATH);
    $url_path = str_replace('/blob', '', $url_path);
    $url = 'https://raw.githubusercontent.com' . $url_path;

    // Read the file.
    if (!($markdown = self::readUrl($url))) {
      Message::make('Couldn\'t read markdown file at @url', ['@url' => $url], Message::ERROR);
      return;
    }

    // Parse elements from front-matter.
    $page_part = explode('---', $markdown);
    if (count($page_part) < 2) {
      Message::make('No front matter in @url', ['@url' => $url_path], Message::DEBUG);
    }
    else {
      try {
        $row = Yaml::parse($page_part[1]);
      }
      catch (ParseException $exception) {
        Message::make('Unable to parse the YAML string: @message', ['@message' => $exception->getMessage()], Message::ERROR);
        return;
      }
    }

    // Migrate metalsmith only and no fully react pages.
    if (empty($row['layout']) || 'page-react.html' == $row['layout']) {
      return;
    }

    // If the record doesn't have an href, get the url from the file path.
    if (empty($row['href'])) {
      // Get the path without the file name for index pages.
      if (preg_match('/\/department-of-veterans-affairs\/vagov-content\/master\/pages\/([^\.]+)\/index\.md/', $url_path, $matches)) {
        $row['url'] = 'https://www.va.gov/' . $matches[1] . '/';
      }
      // Get the path with the file name for all others.
      elseif (preg_match('/\/department-of-veterans-affairs\/vagov-content\/master\/pages\/([^\.]+)\.md/', $url_path, $matches)) {
        $row['url'] = 'https://www.va.gov/' . $matches[1] . '/';
      }
    }
    else {
      // There shouldn't be any with both layout and href, but just in case...
      Message::make('Found href, @href, at @url', ['@href' => $row['href'], '@url' => $url], Message::DEBUG);
      // If it's relative, make it absolute.
      if (substr($row['href'], 0, 1) == '/') {
        $row['url'] = 'https://www.va.gov' . $row['href'];
      }
      // Make sure it's in va.gov (no subdomains).
      elseif (preg_match('/https:\/\/(?:www.)?va.gov\//', $row['href'])) {
        $row['url'] = $row['href'];
      }
      else {
        return;
      }
    }

    // Extract the plainlanguage date, if any.
    if (!empty($row['plainlanguage']) && preg_match('/0?(\d+)[-\.]0?(\d+)[-\.](\d+)/', $row['plainlanguage'], $matches)) {
      $row['plainlanguage_date'] = $matches[1] . '/' . $matches[2] . '/' . $matches[3];
    }

    $this->rows[] = $row;
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
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);

    $contents = curl_exec($handle);
    $http_response_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);

    if ($http_response_code != 200) {
      $message = sprintf('Was unable to load %s, response code: %d', $url, $http_response_code);
      Message::make($message, [], Message::ERROR);
      return FALSE;
    }

    return $contents;
  }

}
