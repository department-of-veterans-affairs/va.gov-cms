<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use Drupal\paragraphs\Entity\Paragraph;
use QueryPath\DOMQuery;

/**
 * Link list item paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class LinksListItem extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return 'li' == $query_path->tag() && $query_path->parent()->hasClass('va-nav-linkslist-list');
  }

  /**
   * {@inheritdoc}
   */
  protected function create(DOMQuery $query_path) {
    $title = $query_path->children('a')->children('.va-nav-linkslist-title')->text();
    if (empty($title)) {
      $title = $query_path->children('a')->children('b')->text();
    }
    return Paragraph::create(
      [
        'type' => 'link_teaser',
        'field_link' => [
          'uri' => $query_path->children('a')->attr('href'),
          'title' => $title,
        ],
        'field_link_summary' => $query_path->children('a')->children('.va-nav-linkslist-description')->text(),
      ]
    );
  }

}
