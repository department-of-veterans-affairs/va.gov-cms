<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;

/**
 * Migration for ReactWidget paragraphs.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class ReactWidget extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function getParagraphName() {
    return 'react_widget';
  }

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return $query_path->attr('id') == 'react-applicationStatus' || $query_path->hasAttr('data-app-id');
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    if ($query_path->attr('id') == 'react-applicationStatus') {
      $cta = FALSE;
      $type = 'health-care-app-status';

      $widget_frontmatter = self::$migrator->row->getSourceProperty('widgets')[0];
      $timeout = $widget_frontmatter['timeout'];
      $loading = $widget_frontmatter['loadingMessage'];
      $error = $widget_frontmatter['errorMessage'];

      $link = $query_path->find('a');
      if ($link->count()) {
        $link_url = $link->attr('href');
        $link_text = $link->text();
        $link_button = $link->hasClass('usa-button-primary');
      }
      else {
        $link_url = '';
        $link_text = '';
        $link_button = FALSE;
      }
    }
    else {
      $cta = TRUE;
      $type = $query_path->attr('data-app-id');

      $timeout = 0;
      $loading = '';
      $error = '';
      $link_url = '';
      $link_text = '';
      $link_button = FALSE;
    }

    if (empty($type)) {
      Message::make('React widget without a type @page: @html',
        [
          '@page' => self::$migrator->row->getDestinationProperty('title'),
          '@html' => $query_path->html(),
        ], Message::ERROR);
    }

    return [
      'field_cta_widget' => $cta,
      'field_default_link' => [
        'uri' => self::toUri($link_url),
        'title' => $link_text,
      ],
      'field_button_format' => $link_button,
      'field_error_message' => $error,
      'field_loading_message' => $loading,
      'field_timeout' => $timeout,
      'field_widget_type' => $type,
    ];

  }

}
