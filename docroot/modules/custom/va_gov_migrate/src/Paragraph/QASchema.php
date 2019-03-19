<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_migrate\Paragraph\Base\QABase;
use QueryPath\DOMQuery;
use Drupal\migration_tools\Message;

/**
 * Q&A Paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class QASchema extends QABase {

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    $question = $query_path->find('[itemprop="name"]');
    return [
      'field_question' => $question->text(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function addChildParagraphs(Paragraph $paragraph, DOMQuery $query_path) {
    $answer = $query_path->find('[itemprop="acceptedAnswer"]');
    if (empty($answer)) {
      Message::make('QA without an answer @page: @html',
        [
          '@page' => self::$migrator->row->getDestinationProperty('title'),
          '@html' => $query_path->html(),
        ], Message::ERROR);
    }

    self::$migrator->addParagraphs($answer->children(), $paragraph, $this->getParagraphField());
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
    return ($query_path->attr('itemtype') == 'http://schema.org/Question');
  }

}
