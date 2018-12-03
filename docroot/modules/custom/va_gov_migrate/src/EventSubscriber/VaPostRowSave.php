<?php
namespace Drupal\va_gov_migrate\EventSubscriber;

use Drupal\migration_tools\EventSubscriber\PostRowSave;
use Drupal\migration_tools\Message;
use QueryPath\Exception;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\MigrateException;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\Entity;

class VaPostRowSave extends PostRowSave {

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\migrate\MigrateException
   */
  public function onMigratePostRowSave(MigratePostRowSaveEvent $event) {
     $row = $event->getRow();
     $html = $row->getSourceProperty('body');
     $url = $row->getSourceProperty('url');
     $nids = $event->getDestinationIdValues();
     $nid = $nids[0];

     try {
       $query_path = htmlqp(mb_convert_encoding($html, "HTML-ENTITIES", "UTF-8"));
     }
     catch (Exception $e) {
       Message::make('Failed to instantiate QueryPath for HTML, Exception: @error_message', ['@error_message' => $e->getMessage()], Message::ERROR);
     }
    // Sometimes queryPath fails.  So one last check.
    if (!is_object($query_path)) {
      throw new MigrateException("{$url} failed to initialize QueryPath");
    }

    // remove wrappers
    while (1 == count($query_path)) {
      $query_path = $query_path->children();
    }

    $paragraph_field = 'field_content_block';
    $node = Node::load($nid);
    $wysiwyg = $this->addParagraphs($query_path,$node, $paragraph_field);
    $this->addWysiwyg($wysiwyg,$node, $paragraph_field);

    parent::onMigratePostRowSave($event);
  }

  /**
   * Step through the query path to find paragraphs. When they're found, attach them
   * to the parent entity. Any content that doesn't belong to other paragraph types
   * goes into wysiwyg paragraphs
   *
   * @param \QueryPath\DOMQuery $query_path
   *  The queryPath to search
   * @param \Drupal\Core\Entity\Entity $parent_entity
   *  The parent entity
   * @param string $parent_field
   *  The machine name of the paragraph field on the parent entity
   * @param string $wysiwyg
   *  The content that will ultimately be added to a wysiwyg paragraph
   *
   * @return string
   *  Any remaining wysiwyg content
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function addParagraphs(\QueryPath\DOMQuery $query_path,
                                   Entity &$parent_entity, $parent_field, $wysiwyg='') {

    /** @var \QueryPath\DOMQuery $element */
    foreach ($query_path as $element) {
      $found_paragraph = FALSE;

      // Starred hr
      if ($element->hasClass('va-h-ruled--stars')) {
        $this->addWysiwyg($wysiwyg, $parent_entity, $parent_field);

        $paragraph = Paragraph::create(['type' => 'starred_horizontal_rule',]);
        $paragraph->save();
        $this->attachParagraph($paragraph,$parent_entity, $parent_field);
        $found_paragraph = TRUE;
      }
      elseif ($element->hasClass('va-nav-linkslist-heading')) {

        $this->addWysiwyg($wysiwyg,$parent_entity, $parent_field);

        $heading = $element->text();
        $paragraph = Paragraph::create([
          'type' => 'list_of_link_teasers',
          'field_title' => $heading,
        ]);
        $paragraph->save();

        $this->attachParagraph($paragraph, $parent_entity, $parent_field);
        $found_paragraph = TRUE;
      }

      if (!$found_paragraph) {
        // these tags might contain paragraphs (and shouldn't contain unwrapped text)
        if (in_array($element->tag(), ['div', 'article', 'section', 'aside'])
          && count($element->children())) {
          // warn about orphaned text
          if ($orphan_text = $element->text()) {
            Message::make('Lost text, "@text", in @file', ['@text' => $orphan_text, '@file' => $parent_entity->url()], Message::WARNING);
          }
          // add the opening tag
          $attr = '';
          foreach ($element->attr() as $name => $value ) {
            $attr .= $name . '="' . $value . '" ';
          }
          $wysiwyg .= "<{$element->tag()} $attr>";
          // look for paragraphs in the children
          $wysiwyg = $this->addParagraphs($element->children(),$parent_entity, $parent_field, $wysiwyg);
          $wysiwyg .= "</{$element->tag()}>";
        }
        elseif (!empty(trim($element->text())) || !empty(trim($element->childrenText()))) {
          $wysiwyg .= $element->html();
        }
      }
    }

    return $wysiwyg;

  }

  /**
   * Attach the paragraph to its parent entity
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *  The paragraph entity to attach
   * @param \Drupal\Core\Entity\Entity $parent_entity
   *  The parent entity
   * @param string $parent_field
   *  The machine name of the paragraph field on the parent entity
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function attachParagraph(Paragraph $paragraph, Entity &$parent_entity, $parent_field) {
    $current = $parent_entity->get($parent_field)->getValue();
    $current[] = [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];
    $parent_entity->set($parent_field, $current);
    $parent_entity->save();
  }

  /**
   * Create a wysiwyg paragraph and attach it to an entity. Clear the $wysiwyg buffer.
   *
   * @param string $wysiwyg
   *  The contents of the paragraph
   * @param \Drupal\Core\Entity\Entity $parent_entity
   *  The parent entity
   * @param string $parent_field
   *  The machine name of the paragraph field on the parent entity
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function addWysiwyg(&$wysiwyg, Entity &$parent_entity, $parent_field) {
    if (!empty($wysiwyg)) {
      $paragraph = Paragraph::create([
        'type' => 'wysiwyg',
        'field_wysiwyg' => [
          "value" => $wysiwyg,
          "format" => "rich_text"
        ]
      ]);
      $paragraph->save();

      $this->attachParagraph($paragraph, $parent_entity, $parent_field);
    }

    $wysiwyg = '';
  }
}