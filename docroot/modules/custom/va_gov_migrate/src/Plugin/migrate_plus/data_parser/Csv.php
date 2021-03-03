<?php

namespace Drupal\va_gov_migrate\Plugin\migrate_plus\data_parser;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Exception\RequirementsException;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate_plus\DataParserPluginBase;
use League\Csv\Reader;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Obtain CSV data for migration.
 *
 * @DataParser(
 *   id = "csv",
 *   title = @Translation("CSV")
 * )
 */
class Csv extends DataParserPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Iterator over the CSV data.
   *
   * @var \Iterator
   */
  protected $iterator;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The location of the current csv being processed.
   *
   * @var string
   */
  protected $currentUrl;

  /**
   * The index of the current row in the CSV being processed.
   *
   * @var int
   */
  protected $currentRowIndex;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger->get('migrate_plus');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')
      );
  }

  /**
   * Retrieves the CSV data and returns it as an array.
   *
   * @param string $url
   *   URL of a CSV feed.
   *
   * @return array
   *   The selected data to be iterated.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  protected function getSourceData($url) {
    $this->currentUrl = $url;
    $data_fetcher = !empty($this->configuration['data_fetcher_plugin']) ? $this->configuration['data_fetcher_plugin'] : NULL;
    $response = $this->getDataFetcherPlugin()->getResponse($url);

    switch ($data_fetcher) {
      case 'http':
        $response_code = $response->getStatusCode();

        if ($response_code === 200) {
          // This fetcher returns an object.
          $content = $response->getBody()->getContents();
          $msg = "Migrating from `@url` ";
          $variables = [
            '@url' => $url,
          ];
          $this->logger->info($msg, $variables);
        }
        else {
          // This request failed. Don't abort the entire migration just log an
          // error and move on to next source.
          $msg = "Could not process `@url` because it returned status @code : @phrase.";
          $variables = [
            '@url' => $url,
            '@code' => $response_code,
            '@phrase' => $response->getReasonPhrase(),
          ];
          $this->logger->error($msg, $variables);
          // Curl was unsuccessful so provide empty content.
          $content = '';
        }
        break;

      case 'file':
        // This fetcher returns a string.
        $content = $response;
        break;

      default:
        // A data fetcher is either not defined or is not supported.
        throw new MigrateException("The 'data_fetcher_plugin' must be specified and only 'file' or 'http' are supported.");
    }

    // Remove items.
    $csv = $this->cleanSource($content);
    $data = $this->parseRows($csv);

    return $data;
  }

  /**
   * Remove noise from the source and get rid of empty lines.
   *
   * @param string $source
   *   The string from the fetcher.
   *
   * @return string
   *   The contents that should be a csv, with items and extra lines removed.
   */
  protected function cleanSource($source = '') {
    // Get any items from config that are called out to remove.
    $replace = (!empty($this->configuration['replace'])) ? (array) $this->configuration['replace'] : [];
    $csv = str_replace(array_keys($replace), array_values($replace), $source);
    // Remove blank lines.
    $csv = preg_replace("/\n\n+/s", "\n", $csv);
    $csv = trim($csv, "\n\r");
    $csv = trim($csv, " ");
    $csv .= "\n";

    $this->swapBreakingCharacters($csv);

    return $csv;
  }

  /**
   * Remove common characters that often lead to PDO exception.
   *
   * @param string $csv
   *   The csv text to clean up by reference.
   */
  private function swapBreakingCharacters(&$csv) {
    // Map based on:
    // https://www.php.net/manual/en/function.mb-convert-encoding.php
    // http://www.htmlentities.com/html/entities/
    $map = [
      chr(0x8A) => chr(0xA9),
      chr(0x8C) => chr(0xA6),
      chr(0x8D) => chr(0xAB),
      chr(0x8E) => chr(0xAE),
      chr(0x8F) => chr(0xAC),
      chr(0x9C) => chr(0xB6),
      chr(0x9D) => chr(0xBB),
      chr(0xA1) => chr(0xB7),
      chr(0xA5) => chr(0xA1),
      chr(0xBC) => chr(0xA5),
      chr(0x9F) => chr(0xBC),
      chr(0xB9) => chr(0xB1),
      chr(0x9A) => chr(0xB9),
      chr(0xBE) => chr(0xB5),
      chr(0x9E) => chr(0xBE),
      chr(0x80) => '&euro;',
      chr(0x82) => '&sbquo;',
      chr(0x84) => '&bdquo;',
      chr(0x85) => '&hellip;',
      chr(0x86) => '&dagger;',
      chr(0x87) => '&Dagger;',
      chr(0x89) => '&permil;',
      chr(0x8B) => '&lsaquo;',
      chr(0x91) => '&lsquo;',
      chr(0x92) => '&rsquo;',
      chr(0x93) => '&ldquo;',
      chr(0x94) => '&rdquo;',
      chr(0x95) => '&bull;',
      chr(0x96) => '&ndash;',
      chr(0x97) => '&mdash;',
      chr(0x99) => '&trade;',
      chr(0x9B) => '&rsquo;',
      chr(0xA6) => '&brvbar;',
      chr(0xA9) => '&copy;',
      chr(0xAB) => '&laquo;',
      chr(0xAE) => '&reg;',
      chr(0xB1) => '&plusmn;',
      chr(0xB5) => '&micro;',
      chr(0xB6) => '&para;',
      chr(0xB7) => '&middot;',
      chr(0xBB) => '&raquo;',
    ];
    $csv = html_entity_decode(mb_convert_encoding(strtr($csv, $map), 'UTF-8', 'ISO-8859-2'), ENT_QUOTES, 'UTF-8');
  }

  /**
   * Parse the csv string into rows.
   *
   * @param string $csv
   *   The string of the contents of the csv file.
   *
   * @return array
   *   An array of arrays of key value pairs that correspond to csv row data.
   */
  protected function parseRows($csv) {
    $delimiter = (!empty($this->configuration['delimiter'])) ? $this->configuration['delimiter'] : ',';
    $enclosure = (!empty($this->configuration['enclosure'])) ? $this->configuration['enclosure'] : '';
    $escape = (!empty($this->configuration['escape'])) ? $this->configuration['escape'] : '';
    $header_offset = (!empty($this->configuration['header_offset'])) ? $this->configuration['header_offset'] : NULL;
    $headers = $this->getHeaders();

    // Create the League CSV reader object to handle parsing and reading.
    if (!class_exists('League\Csv\Reader')) {
      throw new RequirementsException('League CSV Reader is required to use the CSV dataparser.');
    }
    $reader = Reader::createFromString($csv);
    $reader->skipEmptyRecords();
    $reader->setDelimiter($delimiter);
    // Set additional options if we have them.
    if (!empty($enclosure)) {
      $reader->setEnclosure($enclosure);
    }
    if (!empty($escape)) {
      $reader->setEscape($escape);
    }
    if (!empty($header_offset) && is_numeric($header_offset)) {
      $reader->setOffset($header_offset);
    }

    $data = [];
    foreach ($reader->getRecords($headers) as $index => $row) {
      $this->currentRowIndex = $index;
      if ($this->rowGood($row)) {
        // The number of columns is appropriate for the number of fields.
        $data[] = $this->expandRow($row);
      }
    }

    return $data;
  }

  /**
   * Explodes multiple, concatenated values for all cells in a row.
   *
   * @param array $row
   *   The row of CSV cells.
   *
   * @return array
   *   The same row of CSV cells, with each cell's contents exploded.
   */
  public function expandRow(array $row) {
    // Just in case there are multiple values in a cell separated by |.
    foreach ($row as $column_name => $cell_data) {
      // See if we should be sub-parsing any multi-value cells.
      $multi_value_delimiter = (!empty($this->configuration['multi_value_delimiter'])) ? $this->configuration['multi_value_delimiter'] : NULL;
      if (!empty($multi_value_delimiter) && strpos($cell_data, $multi_value_delimiter) !== FALSE) {
        // We have some multi-value delimited content in this cell.
        // Turn it into an array.
        $row[$column_name] = explode('|', $cell_data);
      }
    }

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  protected function openSourceUrl($url) {
    // (Re)open the provided URL.
    $source_data = $this->getSourceData($url);
    $this->iterator = new \ArrayIterator($source_data);
    return TRUE;
  }

  /**
   * Check to see if a row should be used.
   *
   * @param array $row
   *   The array that represents the columns of the row.
   *
   * @return bool
   *   TRUE if the row should be used, FALSE if it should not.
   */
  protected function rowGood(array $row) {
    $keys = (array) $this->configuration['keys'];
    $empty_keys = [];
    foreach ($keys as $key) {
      if (empty(trim($row[$key]))) {
        // This key is empty.
        $empty_keys[] = $key;
      }
    }
    if (!empty($empty_keys)) {
      $msg = "Could not process row @row_num in '@csv' because the keys '@keys' were empty.";
      $variables = [
        '@row_num' => $this->currentRowIndex,
        '@csv' => $this->currentUrl,
        '@keys' => implode(',', $empty_keys),
      ];
      $this->logger->notice($msg, $variables);
      throw new MigrateSkipRowException(t("Could not process row @row_num in '@csv' because the keys '@keys' were empty.", $variables));
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function fetchNextRow() {
    $current = $this->iterator->current();

    if ($current && is_array($current)) {
      foreach ($current as $source_field => $value) {
        $this->currentItem[$source_field] = $value;
      }

      if (!empty($this->configuration['include_raw_data'])) {
        $this->currentItem['raw'] = $current;
      }

      $this->iterator->next();
    }
  }

  /**
   * Extracts the column headers from the migration configuration.
   *
   * @return array
   *   A flat array of source field names/headings.
   */
  private function getHeaders() {
    // These are essentially the column headers defined in the migration config.
    // They have no connection to any header actually in the csv.
    $fields = (!empty($this->configuration['fields'])) ? $this->configuration['fields'] : NULL;

    if ($fields === NULL) {
      // Fields have not been defined and are required.
      throw new MigrateException("Must have 'fields' defined in the migration.");
    }
    else {
      $headers = [];
      foreach ($fields as $field => $data) {
        if (is_array($data)) {
          if (!empty($data['name'])) {
            $headers[] = $data['name'];
          }
          else {
            // This does not have the data needed.  Throw error.
            throw new MigrateException("Each field entry must have a 'name' defined in the migration.");
          }
        }
        else {
          // $data is the field name, because $fields was a flat array
          if (!empty($data)) {
            $headers[] = $data;
          }
          else {
            // This does not have the data needed.  Throw error.
            throw new MigrateException("A field name can not be empty.");
          }
        }
      }
    }

    return $headers;
  }

}
