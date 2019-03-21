<?php

namespace Drupal\va_gov_migrate\Paragraph;

use Drupal\migration_tools\Obtainer\ObtainHtml;
use Drupal\migration_tools\StringTools;
use Drupal\va_gov_migrate\ParagraphType;
use QueryPath\DOMQuery;

/**
 * Q&A Section paragraph type.
 *
 * @package Drupal\va_gov_migrate\Paragraph
 */
class QaSection extends ParagraphType {

  /**
   * {@inheritdoc}
   */
  protected function getParagraphName() {
    return 'q_a_section';
  }

  /**
   * {@inheritdoc}
   */
  protected function isParagraph(DOMQuery $query_path) {
    if (in_array($query_path->tag(), ['h2', 'h3']) && !QAUnstructured::isQuestion($query_path)) {
      $qp = $query_path->next();
      while ($qp->count()) {
        if (substr($qp->tag(), 0, 1) == 'h' && $qp->tag() <= $query_path->tag()) {
          return FALSE;
        }
        if (QAUnstructured::isQuestion($qp) || QASchema::isQuestion($qp)) {
          return TRUE;
        }
        // Some accordions are really Q&As.
        if ($qp->hasClass('usa-accordion')) {
          return QAAccordion::isQaAccordionGroup($qp);
        }

        if ($this->isOtherParagraph($qp)) {
          return FALSE;
        }

        $qp = $qp->next();
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValues(DOMQuery $query_path) {
    $header = $query_path->text();
    $intro_text = '';
    $is_accordion = FALSE;

    // Get any intro text or jump links.
    $qp = $query_path->next();
    while ($qp->count()) {
      // Stop when we get to a question.
      if (QAUnstructured::isQuestion($qp) || QASchema::isQuestion($qp)) {
        break;
      }
      if (QAAccordion::isQaAccordionGroup($qp)) {
        $is_accordion = TRUE;
        break;
      }
      // Stop if it's a h# tag at header level or higher.
      if (substr($qp->tag(), 0, 1) == 'h' && $qp->tag() <= $query_path->tag()) {
        break;
      }

      // If it's a list of jump links, set the accordion flag.
      if ($qp->tag() == 'ul') {
        $first_content = $qp->firstChild()->firstChild();
        if ($first_content->tag() == 'a' && substr($first_content->attr('href'), 0, 1) == '#') {
          $is_accordion = TRUE;
        }
      }
      elseif (StringTools::superTrim($qp->text()) != 'Jump to a section:') {
        // If it survived all the tests, it's intro text.
        if (!empty($intro_text)) {
          // Add line breaks between elements.
          $intro_text .= PHP_EOL . PHP_EOL;
        }
        // Replace br tags with line breaks.
        $intro_text .= strip_tags(implode(PHP_EOL, ObtainHtml::splitOnBr($qp->html())));
      }

      $qp = $qp->next();
    }

    return [
      'field_section_header' => $header,
      'field_section_intro' => $intro_text,
      'field_accordion_display' => $is_accordion,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function isExternalContent(DOMQuery $query_path) {
    // Eat jump link related content.
    if (trim($query_path->text()) == 'Jump to a section:') {
      return TRUE;
    }
    if ($query_path->tag() == 'ul') {
      $first_content = $query_path->firstChild()->firstChild();
      if ($first_content->tag() == 'a' && substr($first_content->attr('href'), 0, 1) == '#') {
        return TRUE;
      }
    }

    // Eat intro text.
    $qp = $query_path->prev();
    while ($qp->count()) {
      // If a previous element may be a Q&A header, see if there are questions.
      if (in_array($qp->tag(), ['h2', 'h3']) && !QAUnstructured::isQuestion($qp)) {
        $qp_next = $query_path->next();
        while ($qp_next->count()) {
          if (QAUnstructured::isQuestion($qp_next) || QASchema::isQuestion($qp_next)
            || QAAccordion::isQaAccordionGroup($qp_next)) {
            return TRUE;
          }
          if (in_array($qp_next->tag(), ['h2', 'h3', 'h4'])) {
            return FALSE;
          }

          if ($this->isOtherParagraph($qp_next)) {
            return FALSE;
          }

          $qp_next = $qp_next->next();
        }
        return FALSE;
      }

      $qp = $qp->prev();
    }
    return FALSE;

  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return 10;
  }

  /**
   * Checks whether a DOM query is a non-qa paragraph.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The query to test.
   *
   * @return bool
   *   Return true if it is another paragraph type.
   */
  protected function isOtherParagraph(DOMQuery $query_path) {
    $paragraph_classes = self::$migrator->getParagraphClasses();
    foreach ($paragraph_classes as $paragraph_class) {
      if (strpos($paragraph_class->getParagraphName(), 'q_a') === 0) {
        continue;
      }
      if ($paragraph_class->isParagraph($query_path)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
