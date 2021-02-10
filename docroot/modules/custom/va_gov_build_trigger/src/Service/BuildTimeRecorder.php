<?php

namespace Drupal\va_gov_build_trigger\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Updates last-built times on content.
 */
class BuildTimeRecorder implements BuildTimeRecorderInterface {

  /**
   * Date Formatter Service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(DateFormatterInterface $dateFormatter, Connection $database) {
    $this->dateFormatter = $dateFormatter;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public function recordBuildTime(int $timestamp = NULL): void {
    if (empty($timestamp)) {
      $timestamp = time();
    }
    $format = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
    $tz = DateTimeItemInterface::STORAGE_TIMEZONE;
    $rawTime = $this->dateFormatter->format($timestamp, 'custom', $format, $tz);
    $sqlTime = strtok($rawTime, '+');

    // We only need to update field table - field is set on node import.
    $nodeQuery = $this->database
      ->update('node__field_page_last_built')
      ->fields([
        'field_page_last_built_value' => $sqlTime,
      ]);
    $nodeQuery->execute();

    // We only need to update - revision field is set on node import.
    $nodeRevisionQuery = $this->database
      ->update('node_revision__field_page_last_built')
      ->fields([
        'field_page_last_built_value' => $sqlTime,
      ]);
    $nodeRevisionQuery->execute();
  }

}
