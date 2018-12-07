<?php

namespace Drupal\va_gov_migrate\EventSubscriber;

use Drupal\migration_tools\EventSubscriber\PostRowSave;
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
    $url = $row->getSourceProperty('url');
    $nids = $event->getDestinationIdValues();

    $node = Node::load($nids[0]);

    // Turn intro text content into plain text.
    $intro_text = $node->get('field_intro_text')->value;
    $intro_text = preg_replace('/<\/p>\s+<p>/', PHP_EOL . PHP_EOL, $intro_text);
    $intro_text = strip_tags($intro_text);
    $node->set('field_intro_text', $intro_text);
    $node->save();

    // Remove Related links paragraph.
    $paragraph_field = 'field_related_links';
    $node->set($paragraph_field, []);
    $node->save();

    // Create the Related links paragraph.
    if ($related_links = $row->getSourceProperty('related_links')) {
      $query_path = $this->createUnwrappedQp($related_links, $url);
      $migrator = new ParagraphMigrator(['LinksList', 'LinksListItem']);
      $migrator->addParagraphs($query_path, $node, $paragraph_field);
    }

    // Remove any existing content block paragraphs (for migrate updates).
    $paragraph_field = 'field_content_block';
    $node->set($paragraph_field, []);
    $node->save();

    // Create Content Block paragraphs.
    $migrator = new ParagraphMigrator(
      [
        'StarredHr',
        'CollapsiblePanel',
        'CollapsiblePanelItem',
        'LinksList',
        'LinksListItem',
      ]
    );
    $html = $row->getSourceProperty('body');
    $query_path = $this->createUnwrappedQp($html, $url);
    $migrator->addParagraphs($query_path, $node, $paragraph_field);
    $migrator->addWysiwyg($node, $paragraph_field);

    parent::onMigratePostRowSave($event);
  }

  /**
   * Creates a query path from html text.
   *
   * @param string $html
   *   The html to build the query path from.
   * @param string $url
   *   The url of the page the query path came from (for error message).
   *
   * @return \QueryPath\DOMQuery
   *   The resulting query path.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public function createUnwrappedQp($html, $url) {
    try {
      $query_path = htmlqp(mb_convert_encoding($html, "HTML-ENTITIES", "UTF-8"));
    }
    catch (Exception $e) {
      \Drupal::logger('va_gov_migrate')->error('Failed to instantiate QueryPath for HTML, Exception: @error_message', ['@error_message' => $e->getMessage()]);
    }
    // Sometimes queryPath fails.  So one last check.
    if (!is_object($query_path)) {
      throw new MigrateException("{$url} failed to initialize QueryPath");
    }

    // Remove wrappers added by htmlqp().
    while (in_array($query_path->tag(), ['html', 'body'])) {
      $query_path = $query_path->children();
    }

    return $query_path;
  }

}
