<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\migration_tools\Message;
use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;

/**
 * ExpandableText paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class ExpandableText extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function getParagraphName() {
    return 'expandable_text';
  }

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return $query_path->hasClass('expander-content');
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    if ($query_path->prev()->attr('id') == 'crisis-expander-link') {
      $expander = $query_path->prev()->text();
    }
    else {
      Message::make('Expanding block missing trigger text: @html',
        ['@html' => $this::$migrator->row->getSourceProperty('alert_title')],
        Message::ERROR);
      $expander = "Show more";
    }
    return
      [
        'field_text_expander' => $expander,
        'field_wysiwyg' => $query_path->find('.expander-content-inner')->innerHTML(),
      ];
  }

  /**
   * {@inheritdoc}
   */
  protected function isExternalContent(DOMQuery $query_path) {
    return $query_path->attr('id') == 'crisis-expander-link';
  }

}
