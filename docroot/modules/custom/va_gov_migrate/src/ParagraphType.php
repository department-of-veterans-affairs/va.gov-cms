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
   * The name of the paragraph field, if any, that contains other paragraphs.
   *
   * This is only used when the paragraphs are of variable type. For paragraphs
   * like List of link teasers, where the child paragraphs are added as part of
   * the paragraph creation, this should be left blank.
   *
   * @var string
   */
  protected $paragraphField = '';
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
