<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use Drupal\paragraphs\Entity\Paragraph;
use QueryPath\DOMQuery;

/**
 * Alert paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class Alert extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return $query_path->hasClass('usa-alert');
  }

  /**
   * {@inheritdoc}
   */
  protected function create(DOMQuery $query_path) {
    if ($query_path->find('.crisis-expander-link')) {
      $alert_type = 'expanding';
    }
    $types = [
      'usa-alert-success' => 'success',
      'usa-alert-warning' => 'warning',
      'usa-alert-error' => 'error',
      'usa-alert-info' => 'information',
      'usa-alert-continue' => 'continue',
      'usa-alert-paragraph' => 'information-paragraph',
      'background-color-only' => 'information-blue',
    ];
    foreach ($types as $class => $type) {
      if ($query_path->hasClass($class)) {
        $alert_type = $type;
        break;
      }
    }

    if (empty($alert_type)) {
      \Drupal::logger('va_gov_migrate')->error('No alert type found for alert, @heading',
        [
          '@heading' => $query_path->find('.usa-alert-heading')->text(),
        ]
      );
      $alert_type = 'success';
    }

    return Paragraph::create(
      [
        'type' => 'subway_map_stop',
        'field_alert_heading' => $query_path->find('.usa-alert-heading')->text(),
        'field_alert_message' => $query_path->find('p')->html(),
        'field_alert_type' => $alert_type,
      ]
    );
  }

}
