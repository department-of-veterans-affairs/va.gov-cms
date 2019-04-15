<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_migrate\Paragraph\Base\QABase;
use QueryPath\DOMQuery;
use Drupal\migration_tools\Message;

/**
 * For converting accordions to Q&A paragraphs.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class QAAccordion extends QABase {

  /**
   * {@inheritdoc}
   */
  protected static function isAccordion() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    $question = $query_path->children('button.usa-accordion-button');
    return [
      'field_question' => $question->text(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function addChildParagraphs(Paragraph $paragraph, DOMQuery $query_path) {
    // Transform the answer into paragraphs.
    $answer = $query_path->children('.usa-accordion-content')->children();
    if (empty($answer)) {
      Message::make('QA without an answer @page: @html',
        [
          '@page' => self::$migrator->row->getDestinationProperty('title'),
          '@html' => $query_path->html(),
        ], Message::ERROR);
    }

    self::$migrator->addParagraphs($answer, $paragraph, $this->getParagraphField());
    self::$migrator->addWysiwyg($paragraph, $this->getParagraphField());
  }

  /**
   * Determines if the html is a question.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The source of the html to test.
   *
   * @return bool
   *   True if it's a question, false if it's not.
   */
  public static function isQuestion(DOMQuery $query_path) {
    // If it's part of an accordion group that contains only questions.
    if ('li' == $query_path->tag() && $query_path->children('button.usa-accordion-button')->count()) {
      $accordion_group = $query_path->parent('.usa-accordion');
      if ($accordion_group->count() && self::isQaAccordionGroup($accordion_group)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Some accordions get migrated as Q&As. See if this is one.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The query path to test.
   *
   * @return bool
   *   True if all of the accordion items are questions.
   */
  public static function isQaAccordionGroup(DOMQuery $query_path) {
    if (!$query_path->hasClass('usa-accordion')) {
      return FALSE;
    }
    $section_titles = $query_path->find('button.usa-accordion-button');
    foreach ($section_titles as $section_title) {
      if (substr($section_title->text(), -1) != '?') {
        return FALSE;
      }
    }
    return TRUE;
  }

}
