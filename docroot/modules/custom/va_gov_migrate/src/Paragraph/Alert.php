<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\migration_tools\Message;
use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;

/**
 * Alert paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class Alert extends ParagraphType {

  /**
   * Descriptions for alert blocks.
   *
   * @var array
   */
  protected $alertBlocks = [
    "How do I get help if I'm homeless or at risk of becoming homeless?" => 4,
    "How do I talk to someone right now?" => 2,
  ];

  /**
   * {@inheritdoc}
   */
  protected function getParagraphName() {
    return 'alert';
  }

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return $query_path->hasClass('usa-alert');
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
  protected function getFieldValues(DOMQuery $query_path) {
    $heading = $query_path->find('.usa-alert-heading')->text();
    if (!empty($this->alertBlocks[$heading])) {
      return [
        'field_alert_block_reference' => $this->alertBlocks[$heading],
      ];
    }

    // Get alert type.
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
      Message::make('No alert type found for alert, @heading on @title, @url',
        [
          '@heading' => $query_path->find('.usa-alert-heading')->text(),
          '@title' => self::$migrator->row->getSourceProperty('title'),
          '@url' => self::$migrator->row->getSourceIdValues()['url'],
        ], Message::ERROR);
      $alert_type = 'information';
    }
    elseif (!in_array($alert_type, ['warning', 'information'])) {
      Message::make('Illegal alert type, @type, found for alert, @heading on @title, @url',
        [
          '@heading' => $heading,
          '@type' => $alert_type,
          '@title' => self::$migrator->row->getSourceProperty('title'),
          '@url' => self::$migrator->row->getSourceIdValues()['url'],
        ], Message::ERROR);
      $alert_type = 'information';
    }
    return [
      'field_alert_heading' => $query_path->find('.usa-alert-heading')->text(),
      'field_alert_type' => $alert_type,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getChildQuery(DOMQuery $query_path) {
    $heading = $query_path->find('.usa-alert-heading')->text();
    $query_path->find('.usa-alert-heading')->remove();

    if (empty($this->alertBlocks[$heading])) {
      return $query_path;
    }

    // Remaining content won't be added to text comparison unless we do it here.
    self::$migrator->endingContent .= $query_path->text();
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function paragraphContent(array $paragraph_fields) {
    // There are child paragraphs, so we need to explicitly count this field.
    return $paragraph_fields['field_alert_heading'];
  }

}
