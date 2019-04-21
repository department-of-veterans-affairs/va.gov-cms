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
   * The name of the error.
   *
   * @var string
   */
  protected static $errFile;

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

    $url = $event->variables['@url'];

    if (array_key_exists('@diff', $event->variables) &&
      $event->type == 'Drupal\va_gov_migrate\ParagraphMigrator') {
      $handle = fopen(self::$rptFile, "a");
      fwrite($handle,
        '"' . $event->variables['@title'] . '",' .
        self::getHub($url) . ',' .
        $event->variables['@field'] . ',' .
        $url . ',' .
        self::getGithubUrl($url) . ',' .
        $event->variables['@percent'] . ',' .
        $event->variables['@diff'] . ',' .
        "\n");
      fclose($handle);
    }
    elseif ($event->severity <= Message::ERROR ||
      ($event->severity == Message::WARNING && $event->type == 'Drupal\va_gov_migrate\ParagraphType')) {
      $handle = fopen(self::$errFile, "a");
      fwrite($handle,
        '"' . $event->message . '","' .
        $event->variables['@title'] . '",' .
        $url . ',' .
        self::getHub($url) . "\n");
      fclose($handle);
    }
    elseif ($event->severity == Message::NOTICE && array_key_exists('@paragraph', $event->variables) &&
      $event->type == 'Drupal\va_gov_migrate\ParagraphType') {
      $handle = fopen("paragraphs.csv", "a");
      fwrite($handle,
        $event->variables['@paragraph'] . ',' .
        $event->variables['@field'] .
        ',"' . $event->variables['@title'] . '",' .
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
    if (preg_match('/https:\/\/www\.va\.gov\/([^\/]*)/', $url, $matches)) {
      return $matches[1];
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

    self::$rptFile = "./migration_analysis_{$event->getMigration()->id()}.csv";
    $handle = fopen(self::$rptFile, "w");
    fwrite($handle, "Title,Hub,Field,Url,Github Url,Percent similarity, Char difference score\n");
    fclose($handle);

    self::$errFile = "migrate_errors_{$event->getMigration()->id()}.csv";
    $handle = fopen(self::$errFile, "w");
    fwrite($handle, "message,title,url,hub\n");
    fclose($handle);
  }

}
