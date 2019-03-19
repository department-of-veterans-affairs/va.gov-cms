<?php

namespace Drupal\va_gov_migrate\Paragraph;

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
    return
      [
        'field_title' => $query_path->children('button.usa-accordion-button')->text(),
        'field_wysiwyg' => [
          "value" => $query_path->children('.usa-accordion-content')->html(),
          "format" => "rich_text",
        ],
      ];
  }

}
