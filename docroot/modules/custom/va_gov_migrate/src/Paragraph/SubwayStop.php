<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use Drupal\paragraphs\Entity\Paragraph;
use QueryPath\DOMQuery;

/**
 * SubwayStop paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class SubwayStop extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return 'li' == $query_path->tag() && $query_path->hasClass('process-step');
  }

  /**
   * {@inheritdoc}
   */
  protected function create(DOMQuery $query_path) {
    return Paragraph::create(
      [
        'type' => 'subway_map_stop',
        'field_wysiwyg' => $query_path->innerHTML(),
      ]
    );
  }

}
