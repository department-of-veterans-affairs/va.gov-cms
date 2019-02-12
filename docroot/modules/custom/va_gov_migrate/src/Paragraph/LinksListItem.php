<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
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
  protected function getParagraphName() {
    return 'link_teaser';
  }

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    if ($query_path->parent()->hasClass('va-nav-linkslist-list')) {
      return 'li' == $query_path->tag();
    }
    elseif ($query_path->parent()->hasClass('hub-page-link-list')) {
      return 'li' == $query_path->tag();
    }
    else {
      return 'section' == $query_path->tag() && $query_path->hasClass('hub-promo-text');
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    if ($query_path->parent()->hasClass('va-nav-linkslist-list') || $query_path->parent()->hasClass('hub-page-link-list')) {
      $title_raw = !empty($query_path->children('a')
        ->children('.va-nav-linkslist-title')
        ->text()) ?
        $query_path->children('a')
          ->children('.va-nav-linkslist-title')
          ->text() : $query_path->children('a')
          ->children('.hub-page-link-list__header')
          ->text();

      $summary_raw = !empty($query_path->children('a')
        ->children('.va-nav-linkslist-description')
        ->text()) ?
        $query_path->children('a')
          ->children('.va-nav-linkslist-description')
          ->text() : $query_path->children('a')
          ->children('.hub-page-link-list__description')
          ->text();

      if (empty($title_raw)) {
        $title_raw = $query_path->children('a')->children('b')->text();
      }
      $url = $query_path->children('a')->attr('href');
    }
    else {
      $title_raw = $query_path->children('h4')->text();
      $summary_raw = $query_path->children('p')->text();
      $url = $query_path->children('h4')->children('a')->attr('href');
    }

    $title = trim($title_raw);
    $summary = trim($summary_raw);

    return [
      'field_link' => [
        'uri' => $url,
        'title' => $title,
      ],
      'field_link_summary' => $summary,
    ];
  }

}
