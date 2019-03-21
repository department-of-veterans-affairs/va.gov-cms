<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;

/**
 * Process Additional Information paragraphs.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class AdditionalInformation extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function getParagraphName() {
    return 'spanish_translation_summary';
  }

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return $query_path->attr('data-widget-type') == 'additional-info';
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    return [
      'field_text_expander' => $query_path->find('.additional-info-title')->text(),
      'field_wysiwyg' => self::toRichText($query_path->find('.additional-info-content')->html()),
    ];
  }

}
