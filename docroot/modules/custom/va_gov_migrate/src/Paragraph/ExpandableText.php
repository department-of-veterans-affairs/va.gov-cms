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
    if (strpos($query_path->prev()->attr('id'), 'expander-link') !== FALSE ||
      strpos($query_path->prev()->firstChild()->attr('id'), 'expander-link') !== FALSE) {
      $expander = $query_path->prev()->text();
    }
    else {
      Message::make('Expanding block missing trigger text: @html',
        ['@html' => self::$migrator->row->getSourceProperty('alert_title')],
        Message::ERROR);
      $expander = "Show more";
    }
    return
      [
        'field_text_expander' => $expander,
        'field_wysiwyg' => self::toRichText($query_path->find('.expander-content-inner')->innerHTML()),
      ];
  }

  /**
   * {@inheritdoc}
   */
  protected function isExternalContent(DOMQuery $query_path) {
    return strpos($query_path->attr('id'), 'expander-link') !== FALSE ||
      strpos($query_path->firstChild()->attr('id'), 'expander-link') !== FALSE;
  }

}
