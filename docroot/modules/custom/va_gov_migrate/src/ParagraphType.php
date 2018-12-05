<?php

namespace Drupal\va_gov_migrate;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\Entity;
use QueryPath\DOMQuery;

/**
 * Abstract base class for migrated paragraph types.
 *
 * @package Drupal\va_gov_migrate
 */
abstract class ParagraphType {
  /**
   * The migrator object.
   *
   * @var \Drupal\va_gov_migrate\ParagraphMigrator
   */
  protected static $migrator;

  /**
   * ParagraphType constructor.
   *
   * @param \Drupal\va_gov_migrate\ParagraphMigrator $migrator
   *   The migrator object.
   */
  public function __construct(ParagraphMigrator $migrator) {
    self::$migrator = $migrator;
  }

  /**
   * Check query path to determine whether it contains a paragraph of this type.
   *
   * If it does, create the paragraph and attach it to the parent entity.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The query path to examine.
   * @param \Drupal\Core\Entity\Entity $entity
   *   The parent entity to attach the paragraph to.
   * @param string $parent_field
   *   The paragraph fiend on the parent entity.
   *
   * @return bool
   *   Returns true if a paragraph was created.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function process(DOMQuery $query_path, Entity $entity, $parent_field) {
    $found = FALSE;

    if ($this->isParagraph($query_path)) {
      self::$migrator->addWysiwyg($entity, $parent_field);

      $paragraph = $this->create($query_path);
      $paragraph->save();

      $this->attachParagraph($paragraph, $entity, $parent_field);

      if ($this->getParagraphField() && count($query_path->children())) {
        self::$migrator->addParagraphs($query_path->children(), $paragraph, $this->getParagraphField());
      }

      $found = TRUE;
    }
    return $found;
  }

  /**
   * Attach the paragraph to its parent entity.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *   The paragraph entity to attach.
   * @param \Drupal\Core\Entity\Entity $entity
   *   The parent entity.
   * @param string $parent_field
   *   The machine name of the paragraph field on the parent entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function attachParagraph(Paragraph $paragraph, Entity &$entity, $parent_field) {
    $current = $entity->get($parent_field)->getValue();
    $current[] = [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];
    $entity->set($parent_field, $current);
    $entity->save();
  }

  /**
   * Return name of the paragraph field, if any, that contains other paragraphs.
   *
   * @return string
   *   The machine name of the paragraph field.
   */
  protected function getParagraphField() {
    return '';
  }

  /**
   * Creates a paragraph object from a query path.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The query pat the create the paragraph from.
   *
   * @return \Drupal\paragraphs\Entity\Paragraph
   *   The newly created paragraph object.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  abstract protected function create(DOMQuery $query_path);

  /**
   * Checks whether a paragraph should be created from the query path.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The query path to test.
   *
   * @return bool
   *   True if the query_path matches the paragraph selector.
   */
  abstract protected function isParagraph(DOMQuery $query_path);

}
