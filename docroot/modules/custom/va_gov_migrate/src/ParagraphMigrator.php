<?php

namespace Drupal\va_gov_migrate;

use Drupal\Core\Entity\Entity;
use Drupal\Driver\Exception\Exception;
use Drupal\paragraphs\Entity\Paragraph;
use QueryPath\DOMQuery;
use Drupal\migrate\MigrateException;

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
   * Create objects from all of the classes in Paragraph/.
   */
  public function __construct() {
    $path = 'modules/custom/va_gov_migrate/src/Paragraph/';
    $paragraph_class_files = glob($path . '*.php');

    foreach ($paragraph_class_files as $file) {
      $class_name = str_replace($path, 'Drupal\\va_gov_migrate\\Paragraph\\', $file);
      $class_name = str_replace('.php', '', $class_name);
      $this->paragraphClasses[] = new $class_name($this);
    }
  }

  /**
   * Create paragraphs from html and attach them to paragraph field on entity.
   *
   * @param string $html
   *   The html that contains the paragraphs.
   * @param \Drupal\Core\Entity\Entity $entity
   *   The parent entity.
   * @param string $paragraph_field
   *   The machine name of the paragraph field on the parent entity.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\migrate\MigrateException
   */
  public function create($html, Entity &$entity, $paragraph_field) {
    // Clear any existing paragraphs - for update.
    $entity->set($paragraph_field, []);
    $entity->save();

    $query_path = $this->createQueryPath($html, $entity);
    $this->addParagraphs($query_path, $entity, $paragraph_field);

    // Add any remaining wysiwyg in the buffer.
    $this->addWysiwyg($entity, $paragraph_field);

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
    // These are tags we shouldn't ignore, even if they're empty.
    $self_contained_tags = '<audio><base><br><embed><form><hr><img><object><progress><svg><video>';

    if (!empty(strip_tags($this->wysiwyg, $self_contained_tags))) {
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

  /**
   * Creates a query path from html text.
   *
   * @param string $html
   *   The html to build the query path from.
   * @param \Drupal\Core\Entity\Entity $entity
   *   The entity the paragraphs are being attached to (for error message).
   *
   * @return \QueryPath\DOMQuery
   *   The resulting query path.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public function createQueryPath($html, Entity $entity) {
    try {
      $query_path = htmlqp(mb_convert_encoding($html, "HTML-ENTITIES", "UTF-8"));
    }
    catch (Exception $e) {
      \Drupal::logger('va_gov_migrate')->error('Failed to instantiate QueryPath for HTML, Exception: @error_message', ['@error_message' => $e->getMessage()]);
    }
    // Sometimes queryPath fails.  So one last check.
    if (!is_object($query_path)) {
      try {
        $url = $entity->toUrl();
      }
      catch (Exception $e) {
        $url = '';
      }
      throw new MigrateException("@url failed to initialize QueryPath", ['@url' => $url]);
    }

    // Remove wrappers added by htmlqp().
    while (in_array($query_path->tag(), ['html', 'body'])) {
      $query_path = $query_path->children();
    }

    return $query_path;
  }

}
