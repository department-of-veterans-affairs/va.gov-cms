<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
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
  protected function getParagraphName() {
    return 'subway_map_stop';
  }

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return 'li' == $query_path->tag() && $query_path->hasClass('process-step');
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    return ['field_wysiwyg' => $query_path->innerHTML()];
  }

}
