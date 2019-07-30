<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;
use Drupal\migration_tools\Message;
use Drupal\migration_tools\StringTools;

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
      if ($query_path->children('a')->children('.va-nav-linkslist-title')->count()) {
        $title_qp = $query_path->children('a')
          ->children('.va-nav-linkslist-title');
      }
      else {
        $title_qp = $query_path->children('a')
          ->children('.hub-page-link-list__header');
      }

      if ($query_path->children('a')->children('.va-nav-linkslist-description')->count()) {
        $summary_qp = $query_path->children('a')->children('.va-nav-linkslist-description');
      }
      else {
        $summary_qp = $query_path->children('a')->children('.hub-page-link-list__description');
      }

      if (!$title_qp->count()) {
        $title_qp = $query_path->children('a')->children('b');
      }
      if (!$title_qp->count()) {
        $title_qp = $query_path->children('a')->children('strong');
      }
      if (!$title_qp->count()) {
        $title_qp = $query_path->children('a')->children('.hub-page-link-list__header');
      }
      $url = $query_path->children('a')->attr('href');
    }
    else {
      $title_qp = $query_path->children('h4');
      $summary_qp = $query_path->children('p');
      $url = $query_path->children('h4')->children('a')->attr('href');
    }

    $title = trim($title_qp->text());
    $summary = StringTools::superTrim($summary_qp->innerHTML());

    if (empty($title)) {
      Message::make('Links list item without a title @page: @html',
        [
          '@page' => self::$migrator->row->getDestinationProperty('title'),
          '@html' => $query_path->html(),
        ], Message::ERROR);
    }
    if (empty($url)) {
      Message::make('Links list item without a link @page: @html',
        [
          '@page' => self::$migrator->row->getDestinationProperty('title'),
          '@html' => $query_path->html(),
        ], Message::ERROR);
    }
    if (empty($summary)) {
      Message::make('Links list item without a summary @page: @html',
        [
          '@page' => self::$migrator->row->getDestinationProperty('title'),
          '@html' => $query_path->html(),
        ], Message::ERROR);
    }

    return [
      'field_link' => [
        'uri' => self::toUri($url),
        'title' => $title,
      ],
      'field_link_summary' => $summary,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function paragraphContent(array $paragraph_fields) {
    return $paragraph_fields['field_link']['title'] . $paragraph_fields['field_link_summary'];
  }

}
