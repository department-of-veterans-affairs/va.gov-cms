<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;

/**
 * Process paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class Process extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function getParagraphName() {
    return 'process';
  }

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return 'ol' == $query_path->tag() && $query_path->hasClass('process');
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    $steps = [];
    foreach ($query_path->children('li') as $child) {
      $steps[] = [
        "value" => $child->innerHTML(),
        "format" => "rich_text",
      ];
    }
    return ['field_steps' => $steps];
  }

}
