<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;

/**
 * Table paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class Table extends ParagraphType {

  private $captionTags = ['h2', 'h3'];

  /**
   * {@inheritdoc}
   */
  protected function getParagraphName() {
    return 'table';
  }

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return $query_path->tag() == 'table';
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    if (in_array($query_path->prev()->tag(), $this->captionTags)) {
      $caption = $query_path->prev()->text();
    }
    $contents = [];
    $rows = $query_path->find('tr');
    foreach ($rows as $row) {
      $contents[] = $this->parseTableRow($row);
    }

    return [
      'field_table' => [
        'value' => $contents,
        'format' => 'rich_text',
        'caption' => $caption,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function isExternalContent(DOMQuery $query_path) {
    return in_array($query_path->tag(), $this->captionTags) && $this->isParagraph($query_path->next());
  }

  /**
   * Return contents of a HTML table row as an array.
   *
   * @param \QueryPath\DOMQuery $html_row
   *   The row to parse.
   *
   * @return array
   *   The resulting array
   */
  protected function parseTableRow(DOMQuery $html_row) {
    $columns = $html_row->find('td, th');
    $return_row = [];
    /** @var \QueryPath\DOMQuery $column */
    foreach ($columns as $column) {
      $return_row[] = $column->text();
    }
    return $return_row;
  }

}
