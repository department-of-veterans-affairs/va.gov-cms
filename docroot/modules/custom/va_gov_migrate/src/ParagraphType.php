<?php

namespace Drupal\va_gov_migrate;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\EntityInterface;
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
   * @param \Drupal\Core\Entity\EntityInterface $entity
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
  public function process(DOMQuery $query_path, EntityInterface $entity, $parent_field, array $allowed_paragraphs) {
    try {
      if ($this->isParagraph($query_path)) {
        if (!$this->allowedParagraph($allowed_paragraphs)) {
          $this->makeNotAllowedMessage($entity, $parent_field);
          return FALSE;
        }

        Message::make('@paragraph added to @title @url',
          [
            '@paragraph' => $this->getParagraphName(),
            '@title' => self::$migrator->row->getSourceProperty('title'),
            '@url' => self::$migrator->row->getSourceIdValues()['url'],
            '@field' => $parent_field,
          ]);

        $paragraph_fields = $this->getFieldValues($query_path);

        self::$migrator->addWysiwyg($entity, $parent_field);

        $paragraph = Paragraph::create(['type' => $this->getParagraphName()] + $paragraph_fields);

        if (!\Drupal::state()->get('va_gov_migrate.dont_migrate_paragraphs')) {
          $paragraph->save();
        }

        static::attachParagraph($paragraph, $entity, $parent_field, $query_path);

        $this->addChildParagraphs($paragraph, $this->getChildQuery($query_path));

        self::$migrator->endingContent .= $this->paragraphContent($paragraph_fields);
        self::$migrator->endingContent .= $this->unmigratedContent();

        return TRUE;
      }
      if ($this->isExternalContent($query_path) && $this->allowedParagraph($allowed_paragraphs)) {
        return TRUE;
      }
    }
    catch (\Exception $e) {
      Message::make("Migration failed for paragraph on @title @parent: @message.",
        [
          '@title' => self::$migrator->row->getSourceProperty('title'),
          '@url' => self::$migrator->row->getSourceIdValues()['url'],
          '@parent' => $entity->bundle(),
          '@message' => $e->getMessage(),
        ]
      );

      return TRUE;
    }

    return FALSE;
  }

  /**
   * Create message for attempt to add a paragraph where it's not allowed.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we tried to attach this paragraph to.
   * @param string $parent_field
   *   The name of the field we tried to attach this paragraph to.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  protected function makeNotAllowedMessage(EntityInterface $entity, $parent_field) {
    $anomaly = "{$this->paragraphLabel()} not allowed on {$this->paragraphLabel($entity->bundle())}";
    if (($this->getParagraphName() === 'q_a' && $entity->bundle() === 'q_a')) {
      $anomaly = AnomalyMessage::Q_A_NESTED;
    }
    elseif ($anomaly === "List of link teasers not allowed on Q&A") {
      $anomaly = AnomalyMessage::MAJOR_LINKS;
    }

    // Link teaser message inevitably follows list of link teasers, so ignore.
    if (($this->getParagraphName() !== 'link_teaser' || $entity->bundle() !== 'q_a')) {
      AnomalyMessage::makeCustom('@class not allowed on @type in field @field on @title @url',
        [
          '@anomaly_type' => $anomaly,
          '@class' => $this->getParagraphName(),
          '@type' => $entity->bundle(),
          '@field' => $parent_field,
          '@title' => self::$migrator->row->getSourceProperty('title'),
          '@url' => self::$migrator->row->getSourceIdValues()['url'],
        ],
        Message::WARNING
      );
    }
  }

  /**
   * Returns best guess of paragraph label based on paragraph machine name.
   *
   * @param string $machine_name
   *   The machine name to build the label from (defaults to paragraph name).
   *
   * @return string
   *   The paragraph label.
   */
  protected function paragraphLabel($machine_name = '') {
    if (empty($machine_name)) {
      $machine_name = $this->getParagraphName();
    }

    $machine_name = str_replace('q_a', 'Q&A', $machine_name);
    return ucfirst(str_replace('_', ' ', $machine_name));
  }

  /**
   * Attach the paragraph to its parent entity.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *   The paragraph entity to attach.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The parent entity.
   * @param string $parent_field
   *   The machine name of the paragraph field on the parent entity.
   * @param \QueryPath\DOMQuery|null $query_path
   *   Optional parameter for the use of children.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function attachParagraph(Paragraph $paragraph, EntityInterface &$entity, $parent_field, DOMQuery $query_path = NULL) {
    if (!\Drupal::state()->get('va_gov_migrate.dont_migrate_paragraphs')) {
      $current = $entity->get($parent_field)->getValue();
      $current[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
      $entity->set($parent_field, $current);
      $entity->save();
    }
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
   * Lets individual paragraphs filter the query to use for child paragraphs.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The current DOM query for this paragraph.
   *
   * @return \QueryPath\DOMQuery
   *   The filtered DOM query.
   */
  protected function getChildQuery(DOMQuery $query_path) {
    return $query_path;
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
  protected function addChildParagraphs(Paragraph $paragraph, DOMQuery $query_path = NULL) {
    if (!$query_path) {
      return;
    }
    // If this paragraph may contain other paragraphs, add them too.
    if (!empty($this->getParagraphField()) && count($query_path->children())) {
      self::$migrator->addParagraphs($query_path->children(), $paragraph, $this->getParagraphField());
      self::$migrator->addWysiwyg($paragraph, $this->getParagraphField());
    }
  }

  /**
   * Returns paragraph content for comparison with source content.
   *
   * @param array $paragraph_fields
   *   An array of fields to be added to drupal paragraphs.
   *
   * @return string
   *   The content.
   */
  protected function paragraphContent(array $paragraph_fields) {
    $paragraph_content = '';

    foreach ($paragraph_fields as $name => $contents) {
      if (is_array($contents) && isset($contents['value'])) {
        $paragraph_content .= $contents['value'];
      }
      else {
        $paragraph_content .= $contents;
      }
    }
    return $paragraph_content;
  }

  /**
   * Returns content that shouldn't be migrated to drupal.
   *
   * @return string
   *   The content.
   */
  protected function unmigratedContent() {
    return '';
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
    if (strpos($text, '<table>') !== FALSE) {
      AnomalyMessage::makeFromRow(AnomalyMessage::TABLES, self::$migrator->row);
    }
    return [
      "value" => $text,
      "format" => "rich_text",
    ];
  }

  /**
   * Checks content for illegal tags and returns html.
   *
   * @param \QueryPath\DOMQuery $query_path
   *   The query containing the content.
   *
   * @return mixed
   *   Inner html as rich text.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public static function toLongText(DOMQuery $query_path) {
    $legal_tags = ['em', 'a', 'strong', 'br', 'p', 'ul', 'li', 'ol'];
    $illegal_tags = [];
    if (!in_array($query_path->tag(), $legal_tags)) {
      $illegal_tags[] = $query_path->tag();
    }
    $children = $query_path->children();
    foreach ($children as $child) {
      if (!in_array($child->tag(), $legal_tags)) {
        $illegal_tags[] = $child->tag();
      }
    }
    if (!empty($illegal_tags)) {
      AnomalyMessage::makeFromRow('Illegal tag(s) in long text', static::$migrator->row, implode(', ', $illegal_tags));
    }

    $text = '';
    if (!empty(trim($query_path->text()))) {
      $text = strip_tags($query_path->html(), '<em><a><strong><br><p><ul><li><ol>');
    }
    return $text;
  }

}
