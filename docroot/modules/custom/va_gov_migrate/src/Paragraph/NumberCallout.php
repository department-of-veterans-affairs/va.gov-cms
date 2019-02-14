<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;

/**
 * NumberCallout paragraph migration.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class NumberCallout extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function getParagraphName() {
    return 'number_callout';
  }

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    if ($query_path->hasClass('card') && $query_path->hasClass('information')) {
      return $query_path->children('.number')->count();
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    return [
      'field_short_phrase_with_a_number' => $query_path->children('.number')->text(),
      'field_wysiwyg' => self::toRichText($query_path->children('.description')->innerHTML()),
    ];
  }

}
