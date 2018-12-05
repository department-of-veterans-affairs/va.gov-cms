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
class StarredHr extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return $query_path->hasClass('va-h-ruled--stars');
  }

  /**
   * {@inheritdoc}
   */
  protected function create(DOMQuery $query_path) {
    return Paragraph::create(['type' => 'starred_horizontal_rule']);
  }

}
