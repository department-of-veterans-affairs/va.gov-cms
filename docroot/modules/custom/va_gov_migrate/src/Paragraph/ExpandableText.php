<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use Drupal\paragraphs\Entity\Paragraph;
use QueryPath\DOMQuery;

/**
 * ExpandableText paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class ExpandableText extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return $query_path->hasClass('additional-info-container');
  }

  /**
   * {@inheritdoc}
   */
  protected function create(DOMQuery $query_path) {
    return Paragraph::create(
      [
        'type' => 'subway_map_stop',
        'field_text_expander' => $query_path->find('.additional-info-title')->text(),
        'field_wysiwyg' => $query_path->find('.additional-info-content')->innerHTML(),
      ]
    );
  }

}
