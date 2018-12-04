<?php

namespace Drupal\va_gov_migrate\EventSubscriber;

use Drupal\migration_tools\EventSubscriber\PostRowSave;
use Drupal\migration_tools\Message;
use QueryPath\Exception;
use Drupal\va_gov_migrate\ParagraphMigrator;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\MigrateException;
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
    $html = $row->getSourceProperty('body');
    $url = $row->getSourceProperty('url');
    $nids = $event->getDestinationIdValues();
    $paragraph_field = 'field_content_block';

    $node = Node::load($nids[0]);

    // Transform intro text paragraphs into newlines.
    $intro_text = $row->getSourceProperty('intro_text');
    $intro_text = $node->get('field_intro_text')->value;
    $intro_text = preg_replace('/<\/p>\s+<p>/', PHP_EOL . PHP_EOL, $intro_text);
    $intro_text = str_replace('<p>', '', $intro_text);
    $intro_text = str_replace('</p>', '', $intro_text);
    $node->set('field_intro_text', $intro_text);
    $node->save();

    try {
      $query_path = htmlqp(mb_convert_encoding($html, "HTML-ENTITIES", "UTF-8"));
    }
    catch (Exception $e) {
      Message::make('Failed to instantiate QueryPath for HTML, Exception: @error_message', ['@error_message' => $e->getMessage()], Message::ERROR);
    }
    // Sometimes queryPath fails.  So one last check.
    if (!is_object($query_path)) {
      throw new MigrateException("{$url} failed to initialize QueryPath");
    }

    // Remove wrappers.
    while (1 == count($query_path)) {
      $query_path = $query_path->children();
    }

    // Remove any existing paragraphs (for migrate updates)
    $node->set($paragraph_field, []);
    $node->save();

    $migrator = new ParagraphMigrator(['StarredHr']);
    $migrator->addParagraphs($query_path, $node, $paragraph_field);
    $migrator->addWysiwyg($node, $paragraph_field);

    parent::onMigratePostRowSave($event);
  }

}
