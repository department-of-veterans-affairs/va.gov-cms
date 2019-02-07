<?php

namespace Drupal\va_gov_migrate\EventSubscriber;

use Drupal\migrate\MigrateSkipRowException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\migrate_plus\Event\MigratePrepareRowEvent;
use Drupal\migrate_plus\Event\MigrateEvents;

/**
 * Add paragraphs to node after save.
 *
 * @package Drupal\va_gov_migrate\EventSubscriber
 */
class PrepareRow implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Give event higher priority so we can filter rows before scraping them.
    return [MigrateEvents::PREPARE_ROW => ['onMigratePrepareRow', 10]];
  }

  /**
   * Fix encoding and filter out unused rows for each migration type.
   *
   * @param \Drupal\migrate_plus\Event\MigratePrepareRowEvent $event
   *   Provided by the listener.
   *
   * @throws \Drupal\migrate\MigrateSkipRowException
   */
  public function onMigratePrepareRow(MigratePrepareRowEvent $event) {
    // Fix encoding mess.
    $event->getRow()->setSourceProperty('title', str_replace('�', "'", $event->getRow()->getSourceProperty('title')));
    $event->getRow()->setSourceProperty('teaser', str_replace('�', "'", $event->getRow()->getSourceProperty('teaser')));

    // Skip files that don't fit specific migration.
    $file_format = $event->getRow()->getSourceProperty('file_format');
    $format = $event->getRow()->getSourceProperty('format');
    $url = $event->getRow()->getSourceProperty('url');

    switch ($event->getMigration()->id()) {
      case 'va_outreach_files':
        if (!in_array($file_format, ['png', 'jpg', 'PDF'])) {
          throw new MigrateSkipRowException(NULL, FALSE);
        }
        break;

      case 'va_outreach_image_media':
        if (!in_array($file_format, ['png', 'jpg']) &&
          strpos($url, 'https://explore.va.gov/share/') !== 0) {
          throw new MigrateSkipRowException(NULL, FALSE);
        }
        break;

      case 'va_outreach_doc_media':
        if ($file_format != 'PDF') {
          throw new MigrateSkipRowException(NULL, FALSE);
        }
        break;

      case 'va_outreach_video':
        if ($format != 'Video') {
          throw new MigrateSkipRowException(NULL, FALSE);
        }
        break;

      case 'va_outreach_embedded_images':
        if (strpos($url, 'https://explore.va.gov/share/') !== 0) {
          throw new MigrateSkipRowException(NULL, FALSE);
        }
        break;
    }
  }

}
