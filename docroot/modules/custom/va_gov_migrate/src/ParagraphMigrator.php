<?php

namespace Drupal\va_gov_migrate;

use Drupal\Core\Entity\Entity;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
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
   * The entity we're adding paragraphs to.
   *
   * @var \Drupal\Core\Entity\Entity
   */
  private $entity;
  /**
   * The row that was just saved.
   *
   * @var \Drupal\migrate\Row
   */
  public $row;

  /**
   * The content to compare with the starting content.
   *
   * @var string
   */
  public $endingContent;

  /**
   * ParagraphImporter constructor.
   *
   * Loads dest entity and creates objects from class files in Paragraph/.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *   The PostRowSave event.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\migrate\MigrateException
   */
  public function __construct(MigratePostRowSaveEvent $event = NULL) {
    if (empty($event)) {
      return;
    }
    $ids = $event->getDestinationIdValues();
    $dest_config = $event->getMigration()->getDestinationConfiguration();
    $dest_plugin = $dest_config['plugin'];
    if (preg_match('/entity:(.+)/', $dest_plugin, $matches)) {
      $dest_entity_id = $matches[1];
      $this->entity = \Drupal::entityTypeManager()->getStorage($dest_entity_id)->load($ids[0]);
    }
    else {
      throw new MigrateException('ParagraphMigrator only works with entities. Destination found: @dest',
        ['@dest' => $dest_plugin]);
    }

    $this->row = $event->getRow();

    $this->endingContent = '';

    $path = 'modules/custom/va_gov_migrate/src/Paragraph/';
    $paragraph_class_files = glob($path . '*.php');

    foreach ($paragraph_class_files as $file) {
      $file_name = str_replace($path, 'Drupal\\va_gov_migrate\\Paragraph\\', $file);
      $class_name = str_replace('.php', '', $file_name);
      $this->paragraphClasses[] = new $class_name($this);
    }

    usort($this->paragraphClasses, function (ParagraphType $a, ParagraphType $b) {
      return $a->getWeight() > $b->getWeight() ? 1 : ($a->getWeight() < $b->getWeight() ? -1 : 0);
    });
  }

  /**
   * Create paragraphs from html and attach them to paragraph field on entity.
   *
   * @param mixed $source_field
   *   The field (or array of fields) to get the paragraph html from.
   * @param string $dest_field
   *   The machine name of the paragraph field on the parent entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\migrate\MigrateException
   */
  public function process($source_field, $dest_field) {
    if (!\Drupal::state()->get('va_gov_migrate.dont_migrate_paragraphs')) {
      $this->deleteExistingParagraphs($this->entity, $dest_field);
    }

    $this->endingContent = '';

    if (is_array($source_field)) {
      $source = '';
      foreach ($source_field as $field) {
        $source .= $this->row->getSourceProperty($field);
      }
    }
    else {
      $source = $this->row->getSourceProperty($source_field);
    }
    try {
      $query_path = $this->createQueryPath($source);
    }
    catch (MigrateException $e) {
      try {
        $url = $this->entity->toUrl();
      }
      catch (\Exception $e) {
        $url = '';
      }

      Message::make('Paragraph migration failed for @field on @url: @error',
        [
          '@field' => $dest_field,
          '@url' => $url,
          '@error' => $e->getMessage(),
        ],
        Message::ERROR);
      return;
    }
    $this->addParagraphs($query_path, $this->entity, $dest_field);

    // Add any remaining wysiwyg in the buffer.
    $this->addWysiwyg($this->entity, $dest_field);

    $sim = similar_text(strip_tags($source), strip_tags($this->endingContent), $percent);

    $source_chars = $this->charMap($source);
    $ending_chars = $this->charMap($this->endingContent);
    $diff = count(array_diff_assoc($source_chars, $ending_chars));

    if ($diff > 0) {
      AnomalyMessage::make('Failed content similarity test', $this->row->getDestinationProperty('title'), reset($this->row->getSourceIdValues()));
    }
  }

  /**
   * Generate array with number of each alphanumeric in string.
   *
   * @param string $source_string
   *   The string to analyze.
   *
   * @return array
   *   Array of character counts.
   */
  protected function charMap($source_string) {
    $source_string = str_replace('&amp;', '&', $source_string);
    $chars = str_split(strip_tags($source_string));
    $result = [];
    foreach ($chars as $char) {
      if (ctype_alnum($char)) {
        if (isset($result[$char])) {
          $result[$char]++;
        }
        else {
          $result[$char] = 1;
        }
      }
    }
    return $result;
  }

  /**
   * Returns all of the paragraph objects used by this migrator.
   *
   * @return array
   *   An array of ParagraphType objects
   */
  public function getParagraphClasses() {
    return $this->paragraphClasses;
  }

  /**
   * INTERNAL FUNCTION - Extract paragraphs and add them to the parent entity.
   *
   * This shouldn't be called directly. Use process() instead.
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
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public function addParagraphs(DOMQuery $query_path, Entity &$parent_entity, $parent_field) {
    try {
      $allowed_paragraphs = self::getAllowedParagraphs($parent_entity, $parent_field);
    }
    catch (MigrateException $e) {
      Message::make('Could not add paragraphs to @field. ' . $e->getMessage(), ['@field' => $parent_field], Message::ERROR);
      return;
    }

    // Wrap any plain text in p tags so it'll work with paragraph testers.
    /** @var \QueryPath\DOMQuery $element */
    foreach ($query_path as $element) {
      // This is just text.
      if (get_class($element->get(0)) != 'DOMElement') {
        if (!empty(trim($element->html()))) {
          $element->after('<p>' . $element->html() . '</p>');
        }
      }
    }

    foreach ($query_path as $element) {
      // We created a wrapped duplicate of plain text above, so ignore it here.
      if (get_class($element->get(0)) != 'DOMElement') {
        continue;
      }
      $found_paragraph = FALSE;

      $num_paragraphs = 0;

      foreach ($this->paragraphClasses as $paragraphClass) {
        $found_paragraph = $paragraphClass->process($element, $parent_entity, $parent_field, $allowed_paragraphs['allowed']);
        if ($found_paragraph) {
          $num_paragraphs++;
          if ($allowed_paragraphs['max'] != -1 && $num_paragraphs > $allowed_paragraphs['max']) {
            Message::make("Too many paragraphs in @title on @entity, field, @field: Maximum: @max, found: @num", [
              '@title' => $this->row->getDestinationProperty('title'),
              '@url' => $this->row->getSourceIdValues()['url'],
              '@entity' => $parent_entity->id(),
              '@field' => $parent_field,
              '@max' => $allowed_paragraphs['max'],
              '@num' => $num_paragraphs,
            ], Message::ERROR);
          }
          break;
        }
      }

      if (!$found_paragraph) {
        // These tags might contain paragraphs
        // (and shouldn't contain unwrapped text).
        $wrapper_tags = ['div', 'article', 'section', 'aside', 'ul'];
        if (in_array($element->tag(), $wrapper_tags) && count($element->children())) {
          // Don't save 'Last updated: <DATE>' line as text (date is in
          // Last Update field).
          if ($element->hasClass('last-updated')) {
            if ($element->children()->count() > 1 || strpos(trim($element->text()), 'Last updated:') !== 0) {
              Message::make('Unexpected content in "last-updated" div in @file',
                ['@file' => $parent_entity->url()], Message::WARNING);
            }
            $this->endingContent .= $element->html();
            continue;
          }

          // This can be removed if no problems pop up.
          if (str_replace(' ', '', $element->text()) !=
            str_replace(' ', '', $element->contents()->text())) {
            Message::make('Text wrapped only in @tag@file: "@text"',
              [
                '@file' => $parent_entity->url() ? ' in ' . $parent_entity->url() : '',
                '@tag' => $element->tag(),
                '@text' => $element->text(),
              ],
              Message::NOTICE);
          }
          else {
            // Add the opening tag.
            $attr = '';
            foreach ($element->attr() as $name => $value) {
              $attr .= $name . '="' . $value . '" ';
            }
            $this->wysiwyg .= "<{$element->tag()} $attr>";
            // Look for paragraphs in the contents.
            $this->addParagraphs($element->contents(), $parent_entity, $parent_field);
            // Add the closing tag.
            $this->wysiwyg .= "</{$element->tag()}>";

          }
        }
        elseif (self::hasContent($element->html())) {
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
    if (self::hasContent($this->wysiwyg)) {
      try {
        list('allowed' => $allowed_paragraphs) = self::getAllowedParagraphs($entity, $parent_field);
      }
      catch (MigrateException $e) {
        Message::make('Could not add paragraphs to @field. ' . $e->getMessage(), ['@field' => $parent_field], Message::ERROR);
        return;
      }

      if (in_array('wysiwyg', $allowed_paragraphs)) {
        $paragraph = Paragraph::create([
          'type' => 'wysiwyg',
          'field_wysiwyg' => ParagraphType::toRichText($this->wysiwyg),
        ]);
        if (!\Drupal::state()->get('va_gov_migrate.dont_migrate_paragraphs')) {
          $paragraph->save();
        }

        ParagraphType::attachParagraph($paragraph, $entity, $parent_field);

        $this->endingContent .= $this->wysiwyg;
      }
      else {
        Message::make('Lost content for field @field on @type @node. Wysiwyg paragraphs not allowed. Content: "@wysiwyg"',
          [
            '@wysiwyg' => $this->wysiwyg,
            '@type' => $entity->bundle(),
            '@field' => $parent_field,
            '@node' => $this->row->getSourceProperty('title'),
            '@url' => $this->row->getSourceIdValues()['url'],
          ],
          Message::ERROR
        );
      }
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
  protected function createQueryPath($html) {
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

    // Remove all spans with ids
    // Corrects problem in html where spans acting as anchors aren't closed.
    /** @var \queryPath\DOMQuery $element */
    foreach ($query_path->find('span[id]') as $element) {
      // This should no longer be a problem (the issue that necessitated this
      // has been addressed, but let's leave this for now, just to make sure.
      if (str_replace(' ', '', $element->text()) !=
        str_replace(' ', '', $element->contents()->text())) {
        Message::make('Text wrapped only in @tag#@id in @title',
          [
            '@title' => $this->row->getSourceProperty('title'),
            '@tag' => $element->tag(),
            '@id' => $element->attr('id'),
          ],
          Message::NOTICE);
      }
    }

    $query_path->find('span[id]')->contents()->unwrap();

    // Remove wrappers added by htmlqp().
    while (in_array($query_path->tag(), ['html', 'body'])) {
      $query_path = $query_path->contents();
    }

    return $query_path;
  }

  /**
   * Gets an array of paragraphs allowed in selected field of selected entity.
   *
   * @param \Drupal\Core\Entity\Entity $entity
   *   The entity that contains the field to check.
   * @param string $field
   *   The field to check.
   *
   * @return array
   *   An array consisting of array of paragraph machine names and max allowed.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public static function getAllowedParagraphs(Entity $entity, $field) {
    $field_defs = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());

    /** @var \Drupal\field\Entity\FieldConfig $field_config */
    $field_config = $field_defs[$field];
    $settings = $field_config->getSettings();
    $max_count = $field_config->get('fieldStorage')->get('cardinality');

    if (empty($field_defs[$field])) {
      throw new MigrateException("$field not found on {$entity->bundle()}");
    }
    $settings = $field_config->getSettings();
    if ($settings['target_type'] != "paragraph") {
      throw new MigrateException("{$entity->getEntityTypeId()} $field is not a paragraph.");
    }

    $available_paragraphs = $settings['handler_settings']['target_bundles_drag_drop'];
    $selected_paragraphs = $settings['handler_settings']['target_bundles'];

    if (empty($selected_paragraphs)) {
      $allowed_paragraphs = array_keys($available_paragraphs);
    }
    elseif ($settings['handler_settings']['negate']) {
      $allowed_paragraphs = array_keys(array_diff_key($available_paragraphs, $selected_paragraphs));
    }
    else {
      $allowed_paragraphs = $selected_paragraphs;
    }

    return [
      'allowed' => $allowed_paragraphs,
      'max' => $max_count,
    ];
  }

  /**
   * Does this html have actual content (not just empty wrappers)?
   *
   * @param string $html
   *   The html to test.
   *
   * @return bool
   *   Returns true if the html has actual content.
   */
  public static function hasContent($html) {
    // These are tags we shouldn't ignore, even if they're empty.
    $self_contained_tags = '<audio><base><embed><form><hr><img><object><progress><svg><video>';

    if (empty(preg_replace('/\s/', '', strip_tags($html, $self_contained_tags)))) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Removes existing paragraphs from the entity and deletes them.
   *
   * @param \Drupal\Core\Entity\Entity $entity
   *   The entity to remove paragraphs from.
   * @param string $paragraph_field
   *   The field that contains the paragraphs.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function deleteExistingParagraphs(Entity $entity, $paragraph_field) {
    // Clear any existing paragraphs.
    $paragraph_targets = $entity->get($paragraph_field)->getValue();
    $entity->set($paragraph_field, []);
    $entity->save();

    if (!empty($paragraph_targets)) {
      $paragraph_ids = [];
      foreach ($paragraph_targets as $paragraph_target) {
        $paragraph_ids[] = $paragraph_target['target_id'];
      }
      $storage_handler = \Drupal::entityTypeManager()->getStorage('paragraph');
      $paragraphs = $storage_handler->loadMultiple($paragraph_ids);
      foreach ($paragraphs as $paragraph) {
        $fields = \Drupal::getContainer()->get("entity_field.manager")->getFieldDefinitions($paragraph->getEntityTypeId(), $paragraph->bundle());
        /** @var \Drupal\Core\Field\FieldDefinitionInterface $field */
        foreach ($fields as $field) {
          if ($field->getSetting('handler') == 'default:paragraph') {
            $this->deleteExistingParagraphs($paragraph, $field->getName());
            break;
          }
        }
      }
      $storage_handler->delete($paragraphs);
    }
  }

}
