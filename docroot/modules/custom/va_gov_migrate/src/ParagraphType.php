<?php

namespace Drupal\va_gov_migrate;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\Entity;
use Drupal\migration_tools\Message;
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
   *   The paragraph field on the parent entity.
   * @param array $allowed_classes
   *   The classes of paragraphs that are allowed in this field.
   *
   * @return bool
   *   Returns true if the current query path is a paragraph or part of one.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public function process(DOMQuery $query_path, Entity $entity, $parent_field, array $allowed_classes = []) {
    try {
      if ($this->isParagraph($query_path)) {
        $this_class = get_class($this);
        // Strip the namespace.
        $this_class = end(explode('\\', $this_class));

        if (!empty($allowed_classes) && !in_array($this_class, $allowed_classes)) {
          Message::make('@class not allowed on @type in field @field: @html',
            [
              '@class' => $this_class,
              '@type' => $entity->bundle(),
              '@field' => $parent_field,
              '@html' => $query_path->html(),
            ],
            Message::ERROR
          );
          return FALSE;
        }

        self::$migrator->addWysiwyg($entity, $parent_field);

        $paragraph = $this->create($query_path);
        $paragraph->save();

        $this->attachParagraph($paragraph, $entity, $parent_field);

        if ($this->getParagraphField() && count($query_path->children())) {
          self::$migrator->addParagraphs($query_path->children(), $paragraph,
            $this->getParagraphField(), $this->getChildClasses());
        }
        return TRUE;
      }
      if ($this->isExternalContent($query_path)) {
        return TRUE;
      }
    }
    catch (\Exception $e) {
      Message::make("Migration failed for paragraph on @parent @url: @message.",
        [
          '@parent' => $entity->bundle(),
          '@url' => $entity->url(),
          '@message' => $e->getMessage(),
        ]
      );

      return TRUE;
    }

    return FALSE;
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
   * If a paragraph has a paragraph field, the classes that it can contain.
   *
   * @return array
   *   The class names of paragraphs the paragraph field can contain.
   *
   *   If empty, there is no restriction on the classes that can be used.
   */
  protected function getChildClasses() {
    return [];
  }

  /**
   * Test query path to see if it's external content.
   *
   * If a query path belongs to a paragraph entity but isn't nested in the same
   * element as the rest of the paragraph, it's external content. No paragraph
   * will be created, but the query path contents won't be added to  the wysiwyg
   * buffer.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The query path to test.
   *
   * @return bool
   *   TRUE if this is external content.
   */
  protected function isExternalContent(DOMQuery $query_path) {
    return FALSE;
  }

  /**
   * Creates a paragraph object from a query path.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The query path to the create the paragraph from.
   *
   * @return \Drupal\paragraphs\Entity\Paragraph
   *   The new paragraph object.
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
