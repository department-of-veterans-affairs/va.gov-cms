<?php

namespace Drupal\va_gov_migrate\EventSubscriber;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\migration_tools\Event\MessageEvent;
use Drupal\migration_tools\Message;
use Drupal\Core\State\StateInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handle message events.
 *
 * @package Drupal\va_gov_migrate\EventSubscriber
 */
class MessageLogger implements EventSubscriberInterface {

  /**
   * The name of the test report file.
   *
   * @var string
   */
  protected static $rptFile;

  /**
   * The name of the error file.
   *
   * @var string
   */
  protected static $errFile;

  /**
   * The name of the paragraph inventory file.
   *
   * @var string
   */
  protected static $paragraphFile;

  /**
   * The name of the paragraph inventory file.
   *
   * @var string
   */
  protected static $anomaliesFile;

  /**
   * The drupal state object.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MessageEvent::EVENT_NAME] = 'onMessage';
    $events[MigrateEvents::PRE_IMPORT] = 'createAnalysisFile';
    return $events;
  }

  /**
   * Outputs a csv for messages of ERROR or higher.
   *
   * @param \Drupal\migration_tools\Event\MessageEvent $event
   *   The event.
   */
  public function onMessage(MessageEvent $event) {
    if (!$this->state->get('va_gov_migrate.create_csv_files')) {
      return;
    }

    if (empty($event->variables['@url'])) {
      $url = '';
    }
    else {
      $url = $event->variables['@url'];
    }

    if (empty($event->variables['@title'])) {
      $title = '';
    }
    else {
      $title = $event->variables['@title'];
    }

    if (empty(self::$rptFile)) {
      return;
    }

    if (array_key_exists('@diff', $event->variables) &&
      $event->type == 'Drupal\va_gov_migrate\ParagraphMigrator') {
      $handle = fopen(self::$rptFile, "a");
      fwrite($handle,
        '"' . $title . '",' .
        self::getHub($url) . ',' .
        $event->variables['@field'] . ',' .
        $url . ',' .
        self::getGithubUrl($url) . ',' .
        $event->variables['@percent'] . ',' .
        $event->variables['@diff'] . ',' .
        "\n");
      fclose($handle);
    }
    elseif (array_key_exists('@anomaly_type', $event->variables)) {
      $handle = fopen(self::$anomaliesFile, "a");
      fwrite($handle,
        '"' . $title . '","' .
        $event->variables['@anomaly_type'] . '",' .
        $url . ',' .
        self::getHub($url) . ',"' .
        $event->variables['@additional_info'] . '"' . "\n");
      fclose($handle);
    }
    elseif ($event->severity <= Message::ERROR ||
      ($event->severity == Message::WARNING && $event->type == 'Drupal\va_gov_migrate\ParagraphType')) {
      $handle = fopen(self::$errFile, "a");
      fwrite($handle,
        '"' . $event->message . '","' .
        $title . '",' .
        $url . ',' .
        self::getHub($url) . "\n");
      fclose($handle);
    }
    elseif ($event->severity == Message::NOTICE && array_key_exists('@paragraph', $event->variables) &&
      $event->type == 'Drupal\va_gov_migrate\ParagraphType') {
      $handle = fopen(self::$paragraphFile, "a");
      fwrite($handle,
        $event->variables['@paragraph'] . ',' .
        $event->variables['@field'] .
        ',"' . $title . '",' .
        $url . ',' .
        self::getHub($url) . "\n");
      fclose($handle);
    }

  }

  /**
   * Gets the hub name from the va.gov url.
   *
   * @param string $url
   *   The va.gov url.
   *
   * @return string
   *   The hub name.
   */
  public static function getHub($url) {
    $url_parts = parse_url($url);
    if ($url_parts['host'] == 'www.va.gov') {
      $path_parts = explode('/', trim($url_parts['path'], '/'));
      if (count($path_parts) > 1) {
        if ($path_parts[0] == 'disability') {
          if ($path_parts[1] == 'eligibility') {
            return 'disability - eligibility';
          }
          return 'disability - everything else';
        }
        return $path_parts[0];
      }
      return 'root';
    }
    return '';
  }

  /**
   * Tries to get github markup url from va.gov url.
   *
   * @param string $url
   *   The va.gov url.
   *
   * @return string
   *   The github url.
   */
  public static function getGithubUrl($url) {
    $path = parse_url($url, PHP_URL_PATH);
    $path = trim($path, '/');
    return 'https://github.com/department-of-veterans-affairs/vagov-content/tree/master/pages/' . $path . '.md';
  }

  /**
   * Create the csv file for text similarity scores for this migration.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The event.
   */
  public function createAnalysisFile(MigrateImportEvent $event) {
    if (!$this->state->get('va_gov_migrate.create_csv_files')) {
      return;
    }

    $rpt_path = parse_url(file_create_url("public://migration_reports"), PHP_URL_PATH);
    // Strip leading slash.
    $rpt_path = substr($rpt_path, 1);
    if (!is_dir($rpt_path)) {
      mkdir($rpt_path);
    }
    self::$rptFile = $rpt_path . "/migration_analysis_{$event->getMigration()->id()}.csv";
    $handle = fopen(self::$rptFile, "w");
    fwrite($handle, "Title,Hub,Field,Url,Github Url,Percent similarity, Char difference score\n");
    fclose($handle);

    self::$errFile = $rpt_path . "/migrate_errors_{$event->getMigration()->id()}.csv";
    $handle = fopen(self::$errFile, "w");
    fwrite($handle, "message,title,url,hub\n");
    fclose($handle);

    self::$paragraphFile = $rpt_path . "/paragraphs_{$event->getMigration()->id()}.csv";
    $handle = fopen(self::$paragraphFile, "w");
    fwrite($handle, "title,field,paragraph,hub,url\n");
    fclose($handle);

    self::$anomaliesFile = $rpt_path . "/anomalies_{$event->getMigration()->id()}.csv";
    $handle = fopen(self::$anomaliesFile, "w");
    fwrite($handle, "title,type,url,hub,additional_info\n");
    fclose($handle);
  }

}
