<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;
use Drupal\migration_tools\Message;

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
    if ($query_path->hasClass('usa-accordion') || $query_path->hasClass('usa-accordion-bordered')) {
      if ($query_path->find('li button.usa-accordion-button')->count() == 0) {
        Message::make('Collapsible panel without any items: @html', ['@html' => $query_path->html()], Message::ERROR);
      }
      return !QAAccordion::isQaAccordionGroup($query_path);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    return ['field_collapsible_panel_multi' => $query_path->hasAttr('aria-multiselectable')];
  }

  /**
   * {@inheritdoc}
   */
  protected function getParagraphField() {
    return 'field_va_paragraphs';
  }

  /**
   * Collapsible panel paragraphs don't add any content.
   *
   * {@inheritDoc}
   */
  protected function paragraphContent(array $paragraph_fields) {
    return '';
  }

}
