<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\migration_tools\Message;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_migrate\Paragraph\Base\QABase;
use QueryPath\DOMQuery;

/**
 * Q&A Paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class QAUnstructured extends QABase {

  /**
   * The heading level of the last question ('1' for h1, '2' for h2, etc).
   *
   * @var string
   */
  protected static $lastQuestionLevel;

  /**
   * {@inheritdoc}
   */
  protected function isExternalContent(DOMQuery $query_path) {
    // The answer is external cotnent.
    //
    // We don't allow HRs in answers.
    if ($query_path->tag() == 'hr') {
      return FALSE;
    }
    // We don't allow links lists in answers.
    if ($this->isLinksList($query_path)) {
      return FALSE;
    }
    // If it's a question, it can't be part of an answer.
    if (self::isQuestion($query_path)) {
      return FALSE;
    }
    // We don't allow jump menus in answers.
    if (QaSection::isJumpMenuHeader($query_path)) {
      return FALSE;
    }
    // If it's a header at or above the question's level, we didn't use it.
    if (substr($query_path->tag(), 0, 1) == 'h' &&
      substr($query_path->tag(), 1) <= self::$lastQuestionLevel) {
      return FALSE;
    }

    // Now let's see if there's a question above it.
    $qp = $query_path->prev();
    while ($qp->count()) {
      if (self::isQuestion($qp)) {
        return TRUE;
      }
      if (substr($qp->tag(), 0, 1) == 'h' &&
        substr($qp->tag(), 1) <= self::$lastQuestionLevel) {
        return FALSE;
      }
      if ($this->isLinksList($qp)) {
        return FALSE;
      }

      $qp = $qp->prev();
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    // Used to test subsequent elements to see if they're part of the answer.
    self::$lastQuestionLevel = substr($query_path->tag(), 1);

    return [
      'field_question' => $query_path->text(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function allowedParagraph(array $allowed_paragraphs) {
    // If we need a q&a section we'll find one or create it on the fly.
    // So fields that only allow q&a sections are fine.
    return in_array('q_a', $allowed_paragraphs) || in_array('q_a_section', $allowed_paragraphs);
  }

  /**
   * {@inheritdoc}
   */
  protected function addChildParagraphs(Paragraph $paragraph, DOMQuery $query_path = NULL) {
    // Generate answer query by starting with an empty div tag and inserting
    // all subsequent sibling tags in the query path
    // until we hit another question, an hr, or any header at or above the
    // question level.
    $answerQuery = qp('<div id="answer"/>')->find('#answer');
    $qp = $query_path->next();
    while ($qp->count()) {
      // Stop if it's another question.
      if (self::isQuestion($qp)) {
        break;
      }
      // Stop it's a header at question level or higher.
      if (substr($qp->tag(), 0, 1) == 'h' && substr($qp->tag(), 1) <= substr($query_path->tag(), 1)) {
        break;
      }
      // Stop if it's a horizontal rule.
      if ($qp->tag() == 'hr') {
        break;
      }
      // Stop if it's a links lists.
      if ($this->isLinksList($qp)) {
        break;
      }
      // Stop if it's a jump menu.
      if (QaSection::isJumpMenuHeader($qp)) {
        break;
      }
      $qp->appendTo($answerQuery);
      $qp = $qp->next();
    }

    // Transform the answerQuery we collected in getFieldValues into paragraphs.
    if (!empty($answerQuery) && $answerQuery->children()->count()) {
      self::$migrator->addParagraphs($answerQuery->children(), $paragraph, $this->getParagraphField());
      self::$migrator->addWysiwyg($paragraph, $this->getParagraphField());
    }
    else {
      Message::make('Question with no answer: @question', ['@question' => $query_path->text()], Message::ERROR);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    // Q&A needs to be processed before other paragraph types so that it can
    // exclude answer content before it's added to the field by another type.
    return -10;
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
    return in_array($query_path->tag(), ['h2', 'h3', 'h4']) && substr($query_path->text(), -1) == '?';
  }

  /**
   * Test whether the current element is or contains a links list.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The element to test.
   *
   * @return bool
   *   Returns true if the current element is or contains a links list.
   */
  protected function isLinksList(DOMQuery $query_path) {
    return $query_path->find('.va-nav-linkslist-heading')->count() > 0;
  }

}
