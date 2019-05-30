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
   * A DOMQuery containing answer content.
   *
   * @var \QueryPath\DOMQuery
   */
  protected static $answerQuery;

  /**
   * A DOMQuery containing content that returned true for isExternalContent.
   *
   * @var \QueryPath\DOMQuery
   */
  protected static $externalContent;

  /**
   * Used for error messages.
   *
   * @var string
   */
  protected static $lastQuestionText;

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
    // If it's a question, it can't be part of an answer.
    if (self::isQuestion($query_path)) {
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
        $query_path->appendTo(self::$externalContent);
        return TRUE;
      }
      if (substr($qp->tag(), 0, 1) == 'h' &&
        substr($qp->tag(), 1) <= self::$lastQuestionLevel) {
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
    // Before we create a new Q&A, make sure the last one worked correctly.
    $this->checkContent();

    // Used to test subsequent elements to see if they're part of the answer.
    self::$lastQuestionLevel = substr($query_path->tag(), 1);
    // Used for error message if excluded content and answer donn't match.
    self::$lastQuestionText = $query_path->text();

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
    self::$answerQuery = qp('<div id="answer"/>')->find('#answer');
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
      $qp->appendTo(self::$answerQuery);
      $qp = $qp->next();
    }

    // Place to collect external content to compare to question content.
    self::$externalContent = qp('<div id="answer"/>')->find('#answer');

    // Transform the answerQuery we collected in getFieldValues into paragraphs.
    if (!empty(self::$answerQuery) && self::$answerQuery->children()->count()) {
      self::$migrator->addParagraphs(self::$answerQuery->children(), $paragraph, $this->getParagraphField());
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
   * Make sure that elements we're using matches what we say we're using.
   *
   * Elements are added to questions in getFieldValues() and reported as being
   * used in isExternalContent(). This checks that the logic matches in both
   * places so we don't lose or duplicate any content.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  protected function checkContent() {
    if (isset(self::$answerQuery) && self::$answerQuery->children()->count() &&
      isset(self::$externalContent) && self::$externalContent->children()->count()) {
      if (self::$answerQuery->html() != self::$externalContent->html()) {
        // Elements were lost.
        if (self::$answerQuery->children()->count() < self::$externalContent->children()->count()) {
          for ($i = 0; $i < self::$answerQuery->children()->count(); $i++) {
            if (self::$answerQuery->children()->get($i) != self::$externalContent->children()->get($i)) {
              $content = self::$externalContent->children()->get($i)->textContent;
              break;
            }
          }
          if (empty($content)) {
            $content = self::$externalContent->children()->get($i)->textContent;
          }

          Message::make('Content was excluded that wasn\'t part of answer for @question on @title, @url',
            [
              '@anomaly_type' => "Q&A - excluded content",
              '@title' => self::$migrator->row->getSourceProperty('title'),
              '@url' => self::$migrator->row->getSourceIdValues()['url'],
              '@question' => self::$lastQuestionText,
            ], Message::ERROR);
        }
        // Elements were duplicated.
        elseif (self::$answerQuery->children()->count() > self::$externalContent->children()->count()) {
          for ($i = 0; $i < self::$externalContent->children()->count(); $i++) {
            if (self::$answerQuery->children()->get($i) != self::$externalContent->children()->get($i)) {
              $content = self::$answerQuery->children()->get($i)->textContent;
              break;
            }
          }
          if (empty($content)) {
            $content = self::$answerQuery->children()->get($i)->textContent;
          }
          Message::make('Content wasn\'t excluded that was part of answer on @question: @content',
            [
              '@question' => self::$lastQuestionText,
              '@content' => $content,
            ], Message::ERROR);
        }
        // We have the same number of elements, but they don't all match.
        else {
          for ($i = 0; $i < self::$answerQuery->children()->count(); $i++) {
            if (self::$answerQuery->children()->get($i) != self::$externalContent->children()->get($i)) {
              $content = self::$answerQuery->children()->get($i)->textContent;
              break;
            }
          }
          Message::make('Excluded content doesn\'t match answer on @question: @content',
            [
              '@question' => self::$lastQuestionText,
              '@content' => $content,
            ], Message::ERROR);
        }
      }
    }
    elseif (isset(self::$answerQuery) && self::$answerQuery->children()->count() &&
      !(isset(self::$externalContent) && self::$externalContent->children()->count())) {
      Message::make('No excluded content. Content wasn\'t excluded that was part of answer on @question: @content',
        [
          '@question' => self::$lastQuestionText,
          '@content' => self::$answerQuery->firstChild()->html(),
        ], Message::ERROR);
    }
    elseif (!(isset(self::$answerQuery) && self::$answerQuery->children()->count()) &&
      isset(self::$externalContent) && self::$externalContent->children()->count()) {
      Message::make('No answer content. Content was excluded that wasn\'t part of answer on @question: @content',
        [
          '@question' => self::$lastQuestionText,
          '@content' => self::$externalContent->firstChild()->html(),
        ], Message::ERROR);
    }
  }

}
