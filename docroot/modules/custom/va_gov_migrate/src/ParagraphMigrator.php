<?php

namespace Drupal\va_gov_migrate;

use Drupal\Core\Entity\Entity;
use Drupal\paragraphs\Entity\Paragraph;
use QueryPath\DOMQuery;

/**
 * ParagraphMigrator migrates paragraphs from query path.
 *
 * @package Drupal\va_gov_migrate
 */
class ParagraphMigrator {

  /**
   * Objects of type ParagraphType.
   *
   * @var array
   */
  private $paragraphClasses = [];
  /**
   * The wysiwyg buffer - holds content that doesn't belong in other paragraphs.
   *
   * @var string
   */
  private $wysiwyg = '';

  /**
   * ParagraphImporter constructor.
   *
   * @param array $paragraph_class_names
   *   The names of ParagraphType classes to create paragraphs from.
   */
  public function __construct(array $paragraph_class_names) {
    foreach ($paragraph_class_names as $class_name) {
      $class_name = 'Drupal\\va_gov_migrate\\Paragraph\\' . $class_name;
      $this->paragraphClasses[] = new $class_name($this);
    }
  }

  /**
   * Turn query path into paragraphs and add them to the parent entity.
   *
   * Step through the query path to find paragraphs. When they're found, attach
   * them to the parent entity. Any content that doesn't belong to any other
   * paragraph type goes into wysiwyg paragraphs.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The queryPath to search.
   * @param \Drupal\Core\Entity\Entity $parent_entity
   *   The parent entity.
   * @param string $parent_field
   *   The machine name of the paragraph field on the parent entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\migrate\MigrateException
   */
  public function addParagraphs(DOMQuery $query_path, Entity &$parent_entity, $parent_field) {

    /** @var \QueryPath\DOMQuery $element */
    foreach ($query_path as $element) {
      $found_paragraph = FALSE;

      foreach ($this->paragraphClasses as $paragraphClass) {
        $found_paragraph = $paragraphClass->process($element, $parent_entity, $parent_field);
        if ($found_paragraph) {
          break;
        }
      }

      if (!$found_paragraph) {
        // These tags might contain paragraphs
        // (and shouldn't contain unwrapped text).
        $wrapper_tags = ['div', 'article', 'section', 'aside', 'ul'];
        if (in_array($element->tag(), $wrapper_tags)
          && count($element->children())) {
          // If the element does contain unwrapped text, that text will be lost.
          if (str_replace(' ', '', $element->text()) !=
              str_replace(' ', '', $element->childrenText())) {
            \Drupal::logger('va_gov_migrate')->error('Lost text, in @file',
              ['@file' => $parent_entity->url()]);
          }
          // Add the opening tag.
          $attr = '';
          foreach ($element->attr() as $name => $value) {
            $attr .= $name . '="' . $value . '" ';
          }
          $this->wysiwyg .= "<{$element->tag()} $attr>";
          // Look for paragraphs in the children.
          $this->addParagraphs($element->children(), $parent_entity, $parent_field);
          $this->wysiwyg .= "</{$element->tag()}>";
        }
        elseif (!empty(trim($element->text()))) {
          $this->wysiwyg .= $element->html();
        }
      }
    }
  }

  /**
   * Create wysiwyg paragraph and attach it to an entity. Clear wysiwyg buffer.
   *
   * @param \Drupal\Core\Entity\Entity $entity
   *   The parent entity.
   * @param string $parent_field
   *   The machine name of the paragraph field on the parent entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function addWysiwyg(Entity &$entity, $parent_field) {
    if (!empty(strip_tags($this->wysiwyg))) {
      $paragraph = Paragraph::create([
        'type' => 'wysiwyg',
        'field_wysiwyg' => [
          "value" => $this->wysiwyg,
          "format" => "rich_text",
        ],
      ]);
      $paragraph->save();

      ParagraphType::attachParagraph($paragraph, $entity, $parent_field);
    }

    $this->wysiwyg = '';
  }

}
