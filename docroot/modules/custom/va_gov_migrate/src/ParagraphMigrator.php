<?php

namespace Drupal\va_gov_migrate;

use Drupal\Core\Entity\Entity;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\MigrateException;
use Drupal\paragraphs\Entity\Paragraph;
use QueryPath\DOMQuery;
use Drupal\migration_tools\Message;
use Drupal\migrate\Row;

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
   * ParagraphImporter constructor.
   *
   * Loads dest entity and creates objects from class files in Paragraph/.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *   The PostRowSave event.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws MigrateException
   */
  public function __construct(MigratePostRowSaveEvent $event) {
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

    $path = 'modules/custom/va_gov_migrate/src/Paragraph/';
    $paragraph_class_files = glob($path . '*.php');

    foreach ($paragraph_class_files as $file) {
      $file_name = str_replace($path, 'Drupal\\va_gov_migrate\\Paragraph\\', $file);
      $class_name = str_replace('.php', '', $file_name);
      $this->paragraphClasses[] = new $class_name($this);
    }

    usort($this->paragraphClasses, function ($a, $b) {
      return $a->getWeight() > $b->getWeight() ? 1 : ($a->getWeight() < $b->getWeight() ? -1 : 0);
    });
  }

  /**
   * Create paragraphs from html and attach them to paragraph field on entity.
   *
   * @param string $source_field
   *   The the name of the field to get the paragraph html from.
   * @param string $dest_field
   *   The machine name of the paragraph field on the parent entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\migrate\MigrateException
   */
  public function process($source_field, $dest_field) {
    $this->deleteExistingParagraphs($this->entity, $dest_field);

    try {
      $query_path = $this->createQueryPath($this->row->getSourceProperty($source_field));
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

    /** @var \QueryPath\DOMQuery $element */
    foreach ($query_path as $element) {
      $found_paragraph = FALSE;

      foreach ($this->paragraphClasses as $paragraphClass) {
        $found_paragraph = $paragraphClass->process($element, $parent_entity, $parent_field, $allowed_paragraphs);
        if ($found_paragraph) {
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
                ['@file' => $parent_entity->url()], Message::ERROR);
            }
            continue;
          }
          // Add the opening tag.
          $attr = '';
          foreach ($element->attr() as $name => $value) {
            $attr .= $name . '="' . $value . '" ';
          }
          $this->wysiwyg .= "<{$element->tag()} $attr>";

          // If the element does contain unwrapped text, that text will be lost.
          if (str_replace(' ', '', $element->text()) !=
            str_replace(' ', '', $element->childrenText())) {
            Message::make('Lost text in @file from @element',
              [
                '@file' => $parent_entity->url(),
                '@element' => "<{$element->tag()} $attr>",
              ],
              Message::ERROR);
          }

          // Look for paragraphs in the children.
          $this->addParagraphs($element->children(), $parent_entity, $parent_field);
          $this->wysiwyg .= "</{$element->tag()}>";
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
        $allowed_paragraphs = self::getAllowedParagraphs($entity, $parent_field);
      }
      catch (MigrateException $e) {
        Message::make('Could not add paragraphs to @field. ' . $e->getMessage(), ['@field' => $parent_field], Message::ERROR);
        return;
      }

      if (in_array('wysiwyg', $allowed_paragraphs)) {
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
      else {
        Message::make('Lost content for field @field on @type @node. Wysiwyg paragraphs not allowed. Content: "@wysiwyg"',
          [
            '@wysiwyg' => $this->wysiwyg,
            '@type' => $entity->bundle(),
            '@field' => $parent_field,
            '@node' => empty($title) ? "" : "on $title",
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

    // Remove all spans with ids
    // Corrects problem in html where spans acting as anchors aren't closed.
    $query_path->find('span[id]')->children()->unwrap();

    // Remove wrappers added by htmlqp().
    while (in_array($query_path->tag(), ['html', 'body'])) {
      $query_path = $query_path->children();
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
   *   An array of paragraph machine names.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public static function getAllowedParagraphs(Entity $entity, $field) {
    $field_defs = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());

    if (empty($field_defs[$field])) {
      throw new MigrateException("$field not found on {$entity->bundle()}");
    }
    $settings = $field_defs[$field]->getSettings();
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

    return $allowed_paragraphs;
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
