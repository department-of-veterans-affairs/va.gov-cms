<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\node\Entity\Node;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Migration: Add "Records" in Topics when "Records" exists in R&S Categories.
 *
 * For R&S content types, if tagged with "Records" in R&S Categories taxonomy,
 * also add "Records" in Topics taxonomy (in Audience & Topics paragraphs).
 *
 * To run: drush codit-batch-operations:run RsRecordsToTopicsMigration
 */
class RsRecordsToTopicsMigration extends BaseRsTagMigration {

  /**
   * The "Records" term name.
   */
  const RECORDS_TERM = 'Records';

  /**
   * Primary category field name.
   */
  const PRIMARY_CATEGORY_FIELD = 'field_primary_category';

  /**
   * Topics field name in Audience & Topics paragraph.
   */
  const TOPICS_FIELD = 'field_topics';

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return 'R&S Content: Add "Records" in Topics when present in R&S Categories';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return 'For R&S content types tagged with "Records" in R&S Categories, also add "Records" in Topics taxonomy.';
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@completed out of @total R&S nodes processed.';
  }

  /**
   * {@inheritdoc}
   */
  public function getItemType(): string {
    return 'R&S content node';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    return $this->gatherNodesByType(
      self::RS_CONTENT_TYPES,
      self::PRIMARY_CATEGORY_FIELD,
      'R&S nodes'
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  protected function processNode(Node $node, array &$sandbox): string {
    // Check if node has "Records" in primary category.
    if (!$this->termExistsInField($node, self::PRIMARY_CATEGORY_FIELD, self::RS_CATEGORIES_VOCABULARY, self::RECORDS_TERM)) {
      return sprintf('Node %d (%s): No "Records" term in R&S Categories.', $node->id(), $node->getTitle());
    }

    // Find "Records" term in Topics taxonomy.
    $records_topic_term = find_term_by_name(self::TOPICS_VOCABULARY, self::RECORDS_TERM);

    if (!$records_topic_term) {
      $this->batchOpLog->appendLog(sprintf('Term "%s" not found in vocabulary %s for node %d', self::RECORDS_TERM, self::TOPICS_VOCABULARY, $node->id()));
      return sprintf('Node %d (%s): "Records" term not found in Topics taxonomy.', $node->id(), $node->getTitle());
    }

    // Check if node has Audience & Topics paragraphs.
    if (!$node->hasField(self::AUDIENCE_TOPICS_FIELD)) {
      return sprintf('Node %d (%s): No Audience & Topics field.', $node->id(), $node->getTitle());
    }

    $field = $node->get(self::AUDIENCE_TOPICS_FIELD);

    if ($field->isEmpty()) {
      // Create a new Audience & Topics paragraph if none exists.
      $paragraph = $this->createAudienceTopicsParagraph('audience_topics', [
        self::TOPICS_FIELD => [['target_id' => $records_topic_term->id()]],
      ]);
      $this->addParagraphToNode($node, self::AUDIENCE_TOPICS_FIELD, $paragraph);
    }
    else {
      // Add "Records" to existing paragraphs.
      $updated = FALSE;
      foreach ($field as $delta => $field_item) {
        /** @var \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem $field_item */
        $paragraph = $field_item->entity;
        if (!$paragraph instanceof ParagraphInterface || !$paragraph->hasField(self::TOPICS_FIELD)) {
          continue;
        }

        if ($this->addTermToParagraphField($paragraph, self::TOPICS_FIELD, $records_topic_term)) {
          // Update the node's field reference to point to the new paragraph
          // revision.
          $this->updateParagraphFieldRevision($node, self::AUDIENCE_TOPICS_FIELD, $delta, $paragraph);
          $updated = TRUE;
        }
      }

      if (!$updated) {
        return sprintf('Node %d (%s): "Records" already present in Topics.', $node->id(), $node->getTitle());
      }
    }

    $log_message = sprintf(
      'R&S tag migration: Added "%s" to Topics taxonomy',
      self::RECORDS_TERM
    );
    $this->saveNodeRevision($node, $log_message);

    return $this->logSuccess($node->id(), $node->getTitle(), 'Successfully added "Records" to Topics.');
  }

}
