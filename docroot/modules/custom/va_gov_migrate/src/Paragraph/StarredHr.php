<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;

/**
 * Starred Horizontal Rule paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class StarredHr extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function getParagraphName() {
    return 'starred_horizontal_rule';
  }

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return $query_path->hasClass('va-h-ruled--stars');
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    return [];
  }

}
