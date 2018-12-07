<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use Drupal\paragraphs\Entity\Paragraph;
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
  protected function isParagraph(DOMQuery $query_path) {
    return $query_path->hasClass('va-nav-linkslist-list');
  }

  /**
   * {@inheritdoc}
   */
  protected function create(DOMQuery $query_path) {
    $title = $query_path->prev()->hasClass('va-nav-linkslist-heading') ? $query_path->prev()->text() : '';
    return Paragraph::create(
      [
        'type' => 'list_of_link_teasers',
        'field_title' => $title,
      ]
    );
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
    return ['LinksListItem'];
  }

  /**
   * {@inheritdoc}
   */
  protected function isExternalContent($query_path) {
    return $query_path->hasClass('va-nav-linkslist-heading');
  }

}
