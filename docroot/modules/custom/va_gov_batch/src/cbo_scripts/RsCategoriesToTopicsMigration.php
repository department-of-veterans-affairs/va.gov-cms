<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\node\Entity\Node;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Migration: Add Topics from R&S Categories (Primary and Additional).
 *
 * For all 7 R&S content types, articles tagged in Primary Category or
 * Additional Category with specific R&S Categories terms are also tagged
 * with the corresponding Topics term in Audience & Topics paragraphs.
 *
 * Category → Topic mapping:
 * - "Decision reviews and appeals" → "Claims and appeals status"
 * - "General benefits information" → "General benefits information"
 * - "PACT Act" → "PACT Act"
 * - "Records" → "Records"
 * - "VA account and profile" → "Account and profile"
 * - "Other topics and questions" → "Other topics and questions"
 *
 * To run: drush codit-batch-operations:run RsCategoriesToTopicsMigration
 */
class RsCategoriesToTopicsMigration extends BaseRsTagMigration {

  /**
   * Primary category field name.
   */
  const PRIMARY_CATEGORY_FIELD = 'field_primary_category';

  /**
   * Additional (other) categories field name.
   */
  const ADDITIONAL_CATEGORY_FIELD = 'field_other_categories';

  /**
   * Topics field name in Audience & Topics paragraph.
   */
  const TOPICS_FIELD = 'field_topics';

  /**
   * Map R&S Category term name → Topics term name.
   *
   * @var array<string, string>
   */
  const CATEGORY_TO_TOPIC_MAP = [
    'Decision reviews and appeals' => 'Claims and appeals status',
    'General benefits information' => 'General benefits information',
    'PACT Act' => 'PACT Act',
    'Records' => 'Records',
    'VA account and profile' => 'Account and profile',
    'Other topics and questions' => 'Other topics and questions',
  ];

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return 'R&S Content: Add Topics from Primary and Additional Categories';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return 'For R&S content types tagged in Primary or Additional Category with specific R&S Categories, also add the corresponding Topics in Audience & Topics.';
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
    return $this->gatherNodesWithCategories();
  }

  /**
   * Gather R&S node IDs with at least one category (primary or additional).
   *
   * @return array
   *   Array of node IDs.
   */
  protected function gatherNodesWithCategories(): array {
    try {
      $or = $this->entityTypeManager->getStorage('node')->getQuery()
        ->condition('type', self::RS_CONTENT_TYPES, 'IN')
        ->accessCheck(FALSE);

      $group = $or->orConditionGroup()
        ->condition(self::PRIMARY_CATEGORY_FIELD, NULL, 'IS NOT NULL')
        ->condition(self::ADDITIONAL_CATEGORY_FIELD, NULL, 'IS NOT NULL');

      return $or->condition($group)->execute();
    }
    catch (\Exception $e) {
      $this->batchOpLog->appendError(sprintf('Error gathering R&S nodes with categories: %s', $e->getMessage()));
      return [];
    }
  }

  /**
   * Get unique R&S Category term names from primary and additional fields.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node.
   *
   * @return string[]
   *   Unique category term names (from lc_categories vocabulary).
   */
  protected function getCategoryTermNames(Node $node): array {
    $names = [];
    foreach ([self::PRIMARY_CATEGORY_FIELD, self::ADDITIONAL_CATEGORY_FIELD] as $field_name) {
      if (!$node->hasField($field_name)) {
        continue;
      }
      $terms = get_node_field_terms($node, $field_name);
      foreach ($terms as $term) {
        if ($term instanceof TermInterface && $term->bundle() === self::RS_CATEGORIES_VOCABULARY) {
          $names[$term->getName()] = TRUE;
        }
      }
    }
    return array_keys($names);
  }

  /**
   * Resolve topic term names to add from category names using the mapping.
   *
   * @param string[] $category_names
   *   Category term names present on the node.
   *
   * @return string[]
   *   Unique Topics term names to add (values from CATEGORY_TO_TOPIC_MAP).
   */
  protected function getTopicNamesToAdd(array $category_names): array {
    $topic_names = [];
    foreach ($category_names as $name) {
      if (isset(self::CATEGORY_TO_TOPIC_MAP[$name])) {
        $topic_names[self::CATEGORY_TO_TOPIC_MAP[$name]] = TRUE;
      }
    }
    return array_keys($topic_names);
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
    $category_names = $this->getCategoryTermNames($node);
    $topic_names = $this->getTopicNamesToAdd($category_names);

    if (empty($topic_names)) {
      return sprintf('Node %d (%s): No mappable R&S Categories in Primary or Additional Category.', $node->id(), $node->getTitle());
    }

    $topic_terms = [];
    foreach ($topic_names as $topic_name) {
      $term = find_term_by_name(self::TOPICS_VOCABULARY, $topic_name);
      if ($term) {
        $topic_terms[] = $term;
      }
      else {
        $this->batchOpLog->appendLog(sprintf('Term "%s" not found in vocabulary %s for node %d', $topic_name, self::TOPICS_VOCABULARY, $node->id()));
      }
    }

    if (empty($topic_terms)) {
      return sprintf('Node %d (%s): No matching Topics terms found.', $node->id(), $node->getTitle());
    }

    if (!$node->hasField(self::AUDIENCE_TOPICS_FIELD)) {
      return sprintf('Node %d (%s): No Audience & Topics field.', $node->id(), $node->getTitle());
    }

    $field = $node->get(self::AUDIENCE_TOPICS_FIELD);

    if ($field->isEmpty()) {
      $paragraph = $this->createAudienceTopicsParagraph('audience_topics', [
        self::TOPICS_FIELD => array_map(function (TermInterface $t) {
          return ['target_id' => $t->id()];
        }, $topic_terms),
      ]);
      $this->addParagraphToNode($node, self::AUDIENCE_TOPICS_FIELD, $paragraph);
    }
    else {
      $updated = FALSE;
      foreach ($field as $delta => $field_item) {
        /** @var \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem $field_item */
        $paragraph = $field_item->entity;
        if (!$paragraph instanceof ParagraphInterface || !$paragraph->hasField(self::TOPICS_FIELD)) {
          continue;
        }

        foreach ($topic_terms as $term) {
          if ($this->addTermToParagraphField($paragraph, self::TOPICS_FIELD, $term)) {
            $this->updateParagraphFieldRevision($node, self::AUDIENCE_TOPICS_FIELD, $delta, $paragraph);
            $updated = TRUE;
          }
        }
      }

      if (!$updated) {
        return sprintf('Node %d (%s): All mapped Topics already present.', $node->id(), $node->getTitle());
      }
    }

    $added_names = array_map(function (TermInterface $t) {
      return $t->getName();
    }, $topic_terms);
    $log_message = sprintf(
      'R&S category-to-topic migration: Added Topics "%s"',
      implode(', ', $added_names)
    );
    $this->saveNodeRevision($node, $log_message);

    return $this->logSuccess($node->id(), $node->getTitle(), sprintf('Added Topics: %s', implode(', ', $added_names)));
  }

}
