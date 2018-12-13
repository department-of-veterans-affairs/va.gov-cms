<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use Drupal\paragraphs\Entity\Paragraph;
use QueryPath\DOMQuery;

/**
 * SubwayMap paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class SubwayMap extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return 'ol' == $query_path->tag() && $query_path->hasClass('process');
  }

  /**
   * {@inheritdoc}
   */
  protected function create(DOMQuery $query_path) {
    return Paragraph::create(['type' => 'subway_map']);
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
  protected function getChildClasses() {
    return ['SubwayStop'];
  }

}
