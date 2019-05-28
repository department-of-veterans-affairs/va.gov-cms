<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\AnomalyMessage;
use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;

/**
 * Table paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class Table extends ParagraphType {

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
    $contents = [];
    $rows = $query_path->find('tr');
    foreach ($rows as $row) {
      $contents[] = $this->parseTableRow($row);
    }

    return [
      'field_table' => [
        'value' => $contents,
        'format' => 'rich_text',
        'caption' => $query_path->find('caption')->text(),
      ],
    ];
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
    $col_tags = ['th', 'td'];
    $return_row = [];
    /** @var \QueryPath\DOMQuery $column */
    foreach ($html_row->children() as $column) {
      if (in_array($column->tag(), $col_tags)) {
        $return_row[] = $column->innerHTML();
      }
      else {
        AnomalyMessage::makeFromRow('Illegal tag in table', self::$migrator->row, $column->innerHTML());
      }
    }
    return $return_row;
  }

  /**
   * {@inheritdoc}
   */
  protected function paragraphContent(array $paragraph_fields) {
    $table = $paragraph_fields['field_table'];
    return implode('', array_merge(...$table['value'])) . $table['caption'];
  }

}
