<?php

namespace Drupal\va_gov_migrate\Paragraph\Base;

use Drupal\Core\Entity\EntityInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_migrate\Paragraph\QAAccordion;
use Drupal\va_gov_migrate\ParagraphMigrator;
use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;

/**
 * Q&A Paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
abstract class QABase extends ParagraphType {

  /**
   * Is this Q&A part of an accordion Q&A section?
   *
   * @return bool
   *   True if its parent section should be accordion.
   */
  protected static function isAccordion() {
    return FALSE;
  }

  /**
   * Is the query a question?
   *
   * This should be overridden by all child classes,
   * but statics can't be abstract.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The query to test.
   *
   * @return bool
   *   True if the query is a question of this type.
   */
  public static function isQuestion(DOMQuery $query_path) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function getParagraphName() {
    return 'q_a';
  }

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    return static::isQuestion($query_path);
  }

  /**
   * {@inheritdoc}
   */
  protected function getParagraphField() {
    return 'field_answer';
  }

  /**
   * {@inheritdoc}
   */
  public static function attachParagraph(Paragraph $paragraph, EntityInterface &$entity, $parent_field, DOMQuery $query_path = NULL) {
    list('allowed' => $allowed_paragraphs) = ParagraphMigrator::getAllowedParagraphs($entity, $parent_field);
    // If this field allows Q&As just add it normally.
    if (in_array('q_a', $allowed_paragraphs)) {
      ParagraphType::attachParagraph($paragraph, $entity, $parent_field);
    }
    // If it only allows Q&A Sections ...
    elseif (in_array('q_a_section', $allowed_paragraphs)) {
      // See if the most recent paragraph on the field is a Q&A Section.
      $qa_section = NULL;
      $paragraph_ids = $entity->get($parent_field)->getValue();
      if (!empty($paragraph_ids)) {
        $last_paragraph = Paragraph::load(end($paragraph_ids)['target_id']);
        if ($last_paragraph->bundle() == 'q_a_section') {
          $qa_section = $last_paragraph;
        }
      }
      // If it's not, create one.
      if (empty($qa_section) || static::needsNewSection($query_path)) {
        $qa_section = Paragraph::create([
          'type' => 'q_a_section',
          'field_accordion_display' => static::isAccordion(),
        ]);
        if (!\Drupal::state()->get('va_gov_migrate.dont_migrate_paragraphs')) {
          $qa_section->save();
        }
        ParagraphType::attachParagraph($qa_section, $entity, $parent_field);
      }
      ParagraphType::attachParagraph($paragraph, $qa_section, 'field_questions');
    }
  }

  /**
   * Test whether a Q&A needs a new section even though there's an existing one.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The path to test.
   *
   * @return bool
   *   TRUE if we need a new Q&A Section.
   */
  protected static function needsNewSection(DOMQuery $query_path = NULL) {
    $needs_section = FALSE;

    // If don't attach Q&A's to accordions they're not a part of.
    if (!empty($query_path) && $query_path->prev()->count() && QAAccordion::isQaAccordionGroup($query_path->prev())) {
      $needs_section = TRUE;
    }

    return $needs_section;
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
  public function getWeight() {
    // Q&A needs to be processed before other paragraph types so that it can
    // exclude answer content before it's added to the field by another type.
    return -5;
  }

}
