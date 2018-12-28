<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;

/**
 * Starred Horizontal Rule paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class CollapsiblePanel extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function getParagraphName() {
    return 'collapsible_panel';
  }

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return $query_path->hasClass('usa-accordion') ||
      $query_path->hasClass('usa-accordion-bordered');
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    $bordered = $query_path->hasClass('usa-accordion-bordered');

    return ['field_collapsible_panel_bordered' => $bordered];
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
    return ['CollapsiblePanelItem'];
  }

}
