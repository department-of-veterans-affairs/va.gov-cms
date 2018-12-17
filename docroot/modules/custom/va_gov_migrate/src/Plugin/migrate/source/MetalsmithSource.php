<?php

namespace Drupal\va_gov_migrate\Plugin\migrate\source;

use Drupal\migration_tools\Message;
use Drupal\migration_tools\Plugin\migrate\source\UrlList;

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
   * Removes any rows with duplicate key fields (url) or invalid urls.
   */
  protected function validateRows() {
    $unique_rows = [];
    $urls = [];
    foreach ($this->rows as $row) {
      if (!in_array($row['url'], $urls)) {
        $unique_rows[] = $row;
        $urls[] = $row['url'];
      }
    }

    $valid_rows = [];

    foreach ($unique_rows as $row) {
      $ok = FALSE;
      $headers = get_headers($row['url']);
      if ($headers) {
        if ($headers[0] == "HTTP/1.1 200 OK") {
          $valid_rows[] = $row;
          $ok = TRUE;
        }
      }
      if (!$ok) {
        \Drupal::logger('va_gov_migrate')->debug('Could not reach @url. Response: @response',
          [
            '@url' => $row['url'],
            '@response' => $headers[0],
          ]
        );
      }
    }
    $this->rows = $valid_rows;
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
      $link_name = 'https://github.com' . $path;
      // If it's a markdown file, process it.
      if (substr($path, -3) == '.md') {
        $this->addRow($link_name);
      }
      // If it's a child directory, recurse into it.
      else {
        $current_path = parse_url($url, PHP_URL_PATH);
        $link_path = parse_url($link_name, PHP_URL_PATH);
        if (strpos($link_path, $current_path) === 0 && $link_path != $current_path) {
          $this->getMarkdownData($link_name);
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
      return;
    }

    // Parse elements from front-matter.
    $row = [];
    $columns = [
      'title',
      'description',
      'layout',
      'collection',
      'spoke',
      'order',
      'template',
      'lastupdate',
      'href',
    ];

    $page_part = explode('---', $markdown);
    if (count($page_part) < 2) {
      \Drupal::logger('va_gov_migrate')->error('No front matter in @url', ['@url' => $url_path]);
    }
    else {
      $front_matter = $page_part[1];

      foreach ($columns as $column) {
        if (preg_match('/^' . $column . ': (.*)/m', $front_matter, $matches)) {
          $row[$column] = $matches[1];
        }
      }
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
      // If it's relative, make it absolute.
      if (substr($row['href'], 0, 1) == '/') {
        $row['url'] = 'https://www.va.gov' . $row['href'];
      }
      else {
        $row['url'] = $row['href'];
      }
    }

    if (preg_match('/https:\/\/(?:www.)?va.gov\//', $row['url'])) {
      $this->rows[] = $row;
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
