<?php

namespace Drupal\va_gov_migrate;

use Drupal\Core\Entity\Entity;
use Drupal\migrate\MigrateException;
use Drupal\paragraphs\Entity\Paragraph;
use QueryPath\DOMQuery;
use Drupal\migration_tools\Message;

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
   * @param array $allowed_classes
   *   The classes of paragraphs that are allowed in this entity/field.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\migrate\MigrateException
   */
  public function create($html, Entity &$entity, $paragraph_field, array $allowed_classes = []) {
    // Clear any existing paragraphs.
    $paragraph_targets = $entity->get($paragraph_field)->getValue();
    if (!empty($paragraph_targets)) {
      $paragraph_ids = [];
      foreach ($paragraph_targets as $paragraph_target) {
        $paragraph_ids[] = $paragraph_target['target_id'];
      }
      $storage_handler = \Drupal::entityTypeManager()->getStorage('paragraph');
      $paragraphs = $storage_handler->loadMultiple($paragraph_ids);
      $storage_handler->delete($paragraphs);

      $entity->set($paragraph_field, []);
      $entity->save();
    }

    try {
      $query_path = $this->createQueryPath($html);
    }
    catch (MigrateException $e) {
      try {
        $url = $entity->toUrl();
      }
      catch (\Exception $e) {
        $url = '';
      }

      Message::make('Paragraph migration failed for @field on @url: @error',
        [
          '@field' => $paragraph_field,
          '@url' => $url,
          '@error' => $e->getMessage(),
        ],
        Message::ERROR);
      return;
    }
    $this->addParagraphs($query_path, $entity, $paragraph_field, $allowed_classes);

    // Add any remaining wysiwyg in the buffer.
    $this->addWysiwyg($entity, $paragraph_field);

  }

  /**
   * INTERNAL FUNCTION - Extract paragraphs and add them to the parent entity.
   *
   * This shouldn't be called directly. Use run() instead.
   *
   * Step through the query path to find paragraphs. When they're found, attach
   * them to the parent entity. Any content that doesn't belong to any other
   * paragraph type goes into the wysiwyg buffer.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The queryPath to search.
   * @param \Drupal\Core\Entity\Entity $parent_entity
   *   The parent entity.
   * @param string $parent_field
   *   The machine name of the paragraph field on the parent entity.
   * @param array $allowed_classes
   *   The classes of paragraphs that are allowed in this entity/field.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public function addParagraphs(DOMQuery $query_path, Entity &$parent_entity, $parent_field, array $allowed_classes = []) {

    /** @var \QueryPath\DOMQuery $element */
    foreach ($query_path as $element) {
      $found_paragraph = FALSE;

      foreach ($this->paragraphClasses as $paragraphClass) {
        $found_paragraph = $paragraphClass->process($element, $parent_entity, $parent_field, $allowed_classes);
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
            Message::make('Lost text, in @file', ['@file' => $parent_entity->url()], Message::ERROR);
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
   * Create wysiwyg paragraph from wysiwyg buffer and empty the buffer.
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
   *
   * @return \QueryPath\DOMQuery
   *   The resulting query path.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public static function createQueryPath($html) {
    try {
      $query_path = htmlqp(mb_convert_encoding($html, "HTML-ENTITIES", "UTF-8"));
    }
    catch (\Exception $e) {
      throw new MigrateException('Failed to instantiate QueryPath: ' . $e->getMessage());
    }
    // Sometimes queryPath fails.  So one last check.
    if (empty($query_path) || !is_object($query_path)) {
      throw new MigrateException("Failed to initialize QueryPath.");
    }

    // Remove wrappers added by htmlqp().
    while (in_array($query_path->tag(), ['html', 'body'])) {
      $query_path = $query_path->children();
    }

    return $query_path;
  }

}
