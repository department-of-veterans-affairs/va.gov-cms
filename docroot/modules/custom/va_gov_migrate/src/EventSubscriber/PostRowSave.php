<?php

namespace Drupal\va_gov_migrate\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\va_gov_migrate\ParagraphMigrator;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\node\Entity\Node;

/**
 * Add paragraphs to node after save.
 *
 * @package Drupal\va_gov_migrate\EventSubscriber
 */
class PostRowSave implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_ROW_SAVE] = 'onMigratePostRowSave';
    return $events;
  }

  /**
   * Perform Post Row Save events.
   *
   *  - Turn intro text into plain text, preserving blank lines between paragraphs
   *  - Create Paragraphs
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\migrate\MigrateException
   */
  public function onMigratePostRowSave(MigratePostRowSaveEvent $event) {
    $row = $event->getRow();
    $nids = $event->getDestinationIdValues();

    $node = Node::load($nids[0]);

    // Turn intro text content into plain text.
    $intro_text = $node->get('field_intro_text')->value;
    $intro_text = preg_replace('/<\/p>\s+<p>/', PHP_EOL . PHP_EOL, $intro_text);
    $intro_text = strip_tags($intro_text);
    $node->set('field_intro_text', $intro_text);
    $node->save();

    $migrator = new ParagraphMigrator($row);

    // Create the Related links paragraph.
    $html = $row->getSourceProperty('related_links');
    $migrator->process($html, $node, 'field_related_links', ['LinksList']);

    // Create Content Block paragraphs.
    $html = $row->getSourceProperty('body');
    $migrator->process($html, $node, 'field_content_block',
      [
        'StarredHr',
        'CollapsiblePanel',
        'LinksList',
        'ExpandableText',
        'SubwayMap',
        'Alert',
      ]
    );

  }

}
