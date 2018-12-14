<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use Drupal\paragraphs\Entity\Paragraph;
use QueryPath\DOMQuery;

/**
 * Starred Horizontal Rule paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class CollapsiblePanelItem extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return 'li' == $query_path->tag() && count($query_path->children('button.usa-accordion-button'));
  }

  /**
   * {@inheritdoc}
   */
  protected function create(DOMQuery $query_path) {
    return Paragraph::create(
      [
        'type' => 'collapsible_panel_item',
        'field_title' => $query_path->children('button.usa-accordion-button')->text(),
        'field_wysiwyg' => [
          "value" => $query_path->children('.usa-accordion-content')->html(),
          "format" => "rich_text",
        ],
      ]
    );
  }

}
