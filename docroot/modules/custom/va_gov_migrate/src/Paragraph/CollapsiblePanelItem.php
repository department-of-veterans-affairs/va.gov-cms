<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;
use Drupal\migration_tools\Message;

/**
 * Starred Horizontal Rule paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class CollapsiblePanelItem extends ParagraphType {

  /**
   * Contains any tables found in the content.
   *
   * @var \QueryPath\DOMQuery
   */
  protected  $tables;

  /**
   * {@inheritdoc}
   */
  protected function getParagraphName() {
    return 'collapsible_panel_item';
  }

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return 'li' == $query_path->tag() && count($query_path->children('button.usa-accordion-button'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    if (!$query_path->children('.usa-accordion-content')->count()) {
      Message::make('Accordion item without content: @title',
        [
          '@title' => $query_path->children('button.usa-accordion-button')->text(),
        ], Message::ERROR);
    }
    $contents = $query_path->children('.usa-accordion-content');

    // Tables are handled as child paragraphs, so we need to wrap them in a
    // parent tag and save them for later.
    if ($contents->find('table')->count()) {
      $tables = $contents->find('table')->remove();
      $this->tables = qp('<div id="tables">' . $tables->html() . '</div>')->find('#tables');
    }
    return [
      'field_title' => $query_path->children('button.usa-accordion-button')->text(),
      'field_wysiwyg' => self::toRichText($contents->html()),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getParagraphField() {
    return 'field_va_paragraphs';
  }

  /**
   * {@inheritdoc}
   */
  protected function addChildParagraphs(Paragraph $paragraph, DOMQuery $query_path = NULL) {
    if (!empty($this->tables)) {
      parent::addChildParagraphs($paragraph, $this->tables);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function paragraphContent(array $paragraph_fields) {
    return $paragraph_fields['field_title'] . $paragraph_fields['field_wysiwyg']['value'];
  }

}
