<?php

namespace Drupal\va_gov_migrate\EventSubscriber;

use Drupal\migration_tools\EventSubscriber\PostRowSave;
use Drupal\va_gov_migrate\ParagraphMigrator;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\node\Entity\Node;

/**
 * Add paragraphs to node after save.
 *
 * @package Drupal\va_gov_migrate\EventSubscriber
 */
class VaPostRowSave extends PostRowSave {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\migrate\MigrateException
   */
  public function onMigratePostRowSave(MigratePostRowSaveEvent $event) {
    $row = $event->getRow();
    $url = $row->getSourceProperty('url');
    $nids = $event->getDestinationIdValues();

    $node = Node::load($nids[0]);

    // Turn intro text content into plain text.
    $intro_text = $node->get('field_intro_text')->value;
    $intro_text = preg_replace('/<\/p>\s+<p>/', PHP_EOL . PHP_EOL, $intro_text);
    $intro_text = strip_tags($intro_text);
    $node->set('field_intro_text', $intro_text);
    $node->save();

    $migrator = new ParagraphMigrator();

    // Create the Related links paragraph.
    $html = $row->getSourceProperty('related_links');
    $migrator->create($html, $node, 'field_related_links', ['LinksList']);

    // Create Content Block paragraphs.
    $html = $row->getSourceProperty('body');
    $migrator->create($html, $node, 'field_content_block',
      [
        'StarredHr',
        'CollapsiblePanel',
        'LinksList',
      ]
    );

    parent::onMigratePostRowSave($event);
  }

}
