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
   * @param array $allowed_paragraphs
   *   The machine names of paragraphs that are allowed in this field.
   *
   * @return bool
   *   Returns true if current query path is a valid paragraph or part of one.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public function process(DOMQuery $query_path, Entity $entity, $parent_field, array $allowed_paragraphs) {
    try {
      if ($this->isParagraph($query_path)) {
        if (!$this->allowedParagraph($allowed_paragraphs)) {
          if ("node" == $entity->getEntityTypeId()) {
            $title = $entity->get('title')->value;
          }

          Message::make('@class not allowed on @type in field @field @node',
            [
              '@class' => $this->getParagraphName(),
              '@type' => $entity->bundle(),
              '@field' => $parent_field,
              '@node' => empty($title) ? html_entity_decode(substr($query_path->html(), 0, 150)) : "on $title",
            ],
            Message::ERROR
          );
          return FALSE;
        }

        self::$migrator->addWysiwyg($entity, $parent_field);

        $paragraph = Paragraph::create(['type' => $this->getParagraphName()] + $this->getFieldValues($query_path));
        $paragraph->save();

        static::attachParagraph($paragraph, $entity, $parent_field);

        $this->addChildParagraphs($paragraph, $query_path);

        return TRUE;
      }
      if ($this->isExternalContent($query_path) && $this->allowedParagraph($allowed_paragraphs)) {
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
   * Returns the machine name of the paragraph this class is generating.
   *
   * @return string
   *   The machine name of the Drupal paragraph.
   */
  abstract protected function getParagraphName();

  /**
   * Generates an associative array of paragraph field values from a query path.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The query path to the create the paragraph from.
   *
   * @return array
   *   an associative array of paragraph field values keyed on field name.
   */
  abstract protected function getFieldValues(DOMQuery $query_path);

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

  /**
   * Determines the order in which this paragraph type processes the query.
   *
   * @return int
   *   The weight of this type. Lower weights are processed earlier
   */
  public function getWeight() {
    return 0;
  }

  /**
   * Checks whether this paragraph is allowed on the parent field.
   *
   * @param array $allowed_paragraphs
   *   An array of the machine names of paragraphs allowed on this field.
   *
   * @return bool
   *   True if this paragraph is allowed, false if it's not.
   */
  protected function allowedParagraph(array $allowed_paragraphs) {
    return in_array($this->getParagraphName(), $allowed_paragraphs);
  }

  /**
   * If the paragraph can contain other paragraphs add them here.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *   The paragraph to attach children to.
   * @param \QueryPath\DOMQuery $query_path
   *   The dom query containing html for the child paragraphs.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  protected function addChildParagraphs(Paragraph $paragraph, DOMQuery $query_path) {
    // If this paragraph may contain other paragraphs, add them too.
    if (!empty($this->getParagraphField()) && count($query_path->children())) {
      self::$migrator->addParagraphs($query_path->children(), $paragraph, $this->getParagraphField());
    }
  }

  /**
   * Adds 'internal' scheme to root-relative urls.
   *
   * @param string $url
   *   The url to process.
   *
   * @return string
   *   The uri.
   */
  public static function toUri($url) {
    if (substr($url, 0, 1) == '/' && substr($url, 0, 2) != '//') {
      $url = 'internal:' . $url;
    }
    return $url;
  }

  /**
   * Returns an array with 'value' and 'format' to assign to a rich text field.
   *
   * We may want to add to this to sanitize the html.
   *
   * @param string $text
   *   The html save to the field.
   *
   * @return array
   *   An array that can be assigned to a rich text field.
   */
  public static function toRichText($text) {
    return [
      "value" => $text,
      "format" => "rich_text",
    ];
  }

}
