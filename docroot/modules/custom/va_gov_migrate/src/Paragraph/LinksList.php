<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;

/**
 * Link list paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class LinksList extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function getParagraphName() {
    return 'list_of_link_teasers';
  }

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    $is_linkslist = FALSE;
    if ($query_path->hasClass('hub-page-link-list') || $query_path->hasClass('va-nav-linkslist-list')) {
      if ($query_path->find('li')->count()) {
        $is_linkslist = TRUE;
      }
    }
    return $is_linkslist;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    $title_raw = '';
    if ($query_path->prev()->hasClass('va-nav-linkslist-heading') || $query_path->prev()->hasClass('hub-page-link-list__title')) {
      $title_raw = !empty($query_path->prev()->text()) ? $query_path->prev()->text() : '';
    }
    $title = trim($title_raw);
    return ['field_title' => $title];
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
  protected function isExternalContent(DOMQuery $query_path) {
    return $query_path->hasClass('hub-page-link-list__title') ? $query_path->hasClass('hub-page-link-list__title') : $query_path->hasClass('va-nav-linkslist-heading');
  }

}
