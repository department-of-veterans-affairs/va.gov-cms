<?php

namespace Drupal\va_gov_batch\cbo_scripts;

require_once __DIR__ . '/../../../../../../scripts/content/script-library.php';

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Base class for R&S tag migration scripts.
 *
 * Provides common functionality for R&S taxonomy migration operations.
 */
abstract class BaseRsTagMigration extends BatchOperations implements BatchScriptInterface {

  /**
   * R&S content types.
   *
   * @var array
   */
  const RS_CONTENT_TYPES = [
    'checklist',
    'faq_multiple_q_a',
    'media_list_images',
    'support_resources_detail_page',
    'q_a',
    'step_by_step',
    'media_list_videos',
  ];

  /**
   * R&S Categories taxonomy vocabulary ID.
   *
   * @var string
   */
  const RS_CATEGORIES_VOCABULARY = 'lc_categories';

  /**
   * Audience - Beneficiaries taxonomy vocabulary ID.
   *
   * @var string
   */
  const AUDIENCE_VOCABULARY = 'audience_beneficiaries';

  /**
   * Topics taxonomy vocabulary ID.
   *
   * @var string
   */
  const TOPICS_VOCABULARY = 'topics';

  /**
   * Audience & Topics paragraph field name.
   *
   * @var string
   */
  const AUDIENCE_TOPICS_FIELD = 'field_audience_topics';

  /**
   * Get field definition for a content type.
   *
   * @param string $entity_type
   *   The entity type (e.g., 'node').
   * @param string $bundle
   *   The bundle (content type).
   * @param string $field_name
   *   The field name.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface|null
   *   The field definition or NULL if not found.
   */
  protected function getFieldDefinition(string $entity_type, string $bundle, string $field_name): ?FieldDefinitionInterface {
    $field_definitions = $this->entityFieldManager->getFieldDefinitions($entity_type, $bundle);
    return $field_definitions[$field_name] ?? NULL;
  }

  /**
   * Get field cardinality.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle.
   * @param string $field_name
   *   The field name.
   *
   * @return int
   *   The field cardinality (-1 for unlimited).
   */
  protected function getFieldCardinality(string $entity_type, string $bundle, string $field_name): int {
    $field_definition = $this->getFieldDefinition($entity_type, $bundle, $field_name);
    if ($field_definition) {
      return $field_definition->getFieldStorageDefinition()->getCardinality();
    }
    return 1;
  }

  /**
   * Get taxonomy vocabulary ID from a field.
   *
   * When multiple vocabularies are configured, only the first vocabulary
   * is returned. A warning is logged to indicate this behavior.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle.
   * @param string $field_name
   *   The field name.
   *
   * @return string|null
   *   The vocabulary ID or NULL if not found. When multiple vocabularies
   *   are configured, returns the first vocabulary ID.
   */
  protected function getFieldTaxonomyVocabulary(string $entity_type, string $bundle, string $field_name): ?string {
    $field_definition = $this->getFieldDefinition($entity_type, $bundle, $field_name);
    if (!$field_definition) {
      return NULL;
    }

    $settings = $field_definition->getSettings();
    if (isset($settings['handler_settings']['target_bundles'])) {
      $target_bundles = $settings['handler_settings']['target_bundles'];
      $target_bundles_count = count($target_bundles);

      if ($target_bundles_count === 1) {
        return reset($target_bundles);
      }
      elseif ($target_bundles_count > 1) {
        // Multiple vocabularies configured - return first and log warning.
        $first_vocabulary = reset($target_bundles);
        $all_vocabularies = implode(', ', $target_bundles);
        $this->batchOpLog->appendLog(sprintf('Field %s on %s:%s has multiple target vocabularies (%s). Returning only the first: %s', $field_name, $entity_type, $bundle, $all_vocabularies, $first_vocabulary));
        return $first_vocabulary;
      }
    }

    return NULL;
  }

  /**
   * Add terms to a node field (additive, not overwriting).
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node.
   * @param string $field_name
   *   The field name.
   * @param \Drupal\taxonomy\TermInterface[] $terms_to_add
   *   Terms to add.
   *
   * @return bool
   *   TRUE if terms were added, FALSE otherwise.
   */
  protected function addTermsToNodeField(Node $node, string $field_name, array $terms_to_add): bool {
    if (!$node->hasField($field_name)) {
      $this->batchOpLog->appendError(sprintf('Field %s does not exist on node %d', $field_name, $node->id()));
      return FALSE;
    }

    $existing_terms = get_node_field_terms($node, $field_name);
    $existing_tids = array_map(function (TermInterface $term) {
      return $term->id();
    }, $existing_terms);

    $new_terms = [];
    foreach ($terms_to_add as $term) {
      if (!in_array($term->id(), $existing_tids)) {
        $new_terms[] = $term;
      }
    }

    if (empty($new_terms)) {
      return FALSE;
    }

    // Get cardinality limit.
    $cardinality = $this->getFieldCardinality('node', $node->bundle(), $field_name);
    $existing_count = count($existing_terms);

    // Calculate how many new terms can fit.
    $terms_to_add_count = count($new_terms);
    $terms_added = [];
    $terms_excluded = [];

    if ($cardinality > 0) {
      $available_slots = $cardinality - $existing_count;
      if ($available_slots <= 0) {
        // No room for new terms.
        $terms_excluded = $new_terms;
        $excluded_terms_list = implode(', ', array_map(function (TermInterface $term) {
          return $term->getName();
        }, $terms_excluded));
        $this->batchOpLog->appendLog(sprintf('Field %s on node %d is at cardinality limit (%d). Cannot add %d new term(s). Excluded terms: %s', $field_name, $node->id(), $cardinality, $terms_to_add_count, $excluded_terms_list));
        return FALSE;
      }

      // Only add as many new terms as fit.
      $terms_to_add_final = array_slice($new_terms, 0, $available_slots);
      $terms_added = $terms_to_add_final;
      $terms_excluded = array_slice($new_terms, $available_slots);
    }
    else {
      // Unlimited cardinality, add all terms.
      $terms_added = $new_terms;
    }

    // Get current field values and add the terms that fit.
    $field_values = $node->get($field_name)->getValue();
    foreach ($terms_added as $term) {
      $field_values[] = ['target_id' => $term->id()];
    }

    // Log warning if some terms were excluded.
    if (!empty($terms_excluded)) {
      $excluded_terms_list = implode(', ', array_map(function (TermInterface $term) {
        return $term->getName();
      }, $terms_excluded));
      $added_count = count($terms_added);
      $excluded_count = count($terms_excluded);
      $this->batchOpLog->appendLog(sprintf('Field %s on node %d: Added %d of %d new term(s) due to cardinality limit (%d). Excluded %d term(s): %s', $field_name, $node->id(), $added_count, $terms_to_add_count, $cardinality, $excluded_count, $excluded_terms_list));
    }

    $node->set($field_name, $field_values);
    return TRUE;
  }

  /**
   * Load a node and return it, or NULL if not found.
   *
   * @param int $nid
   *   The node ID.
   *
   * @return \Drupal\node\Entity\Node|null
   *   The default revision of that node, or NULL if not found.
   */
  protected function loadNode(int $nid): ?Node {
    return get_node_at_default_revision($nid);
  }

  /**
   * {@inheritdoc}
   */
  public function saveNodeRevision(NodeInterface $node, $message = '', $new = TRUE, ?callable $apply_to_forward_revision = NULL): int {
    return save_node_revision($node, $message, $new, $apply_to_forward_revision);
  }

  /**
   * Handle exception during node processing.
   *
   * @param mixed $item
   *   The item being processed (usually node ID).
   * @param \Exception $e
   *   The exception.
   *
   * @return string
   *   Error message string.
   */
  protected function handleProcessingError(mixed $item, \Exception $e): string {
    $message = sprintf('Error processing node ID %s: %s', $item, $e->getMessage());
    $this->batchOpLog->appendError($message);
    return sprintf('Error processing node %s.', $item);
  }

  /**
   * Check if node exists, return error message if not.
   *
   * @param mixed $item
   *   The item being processed (usually node ID).
   * @param \Drupal\node\Entity\Node|null $node
   *   The loaded node or NULL if not found.
   *
   * @return string|null
   *   Error message if node not found, NULL otherwise.
   */
  protected function checkNodeExists(mixed $item, ?Node $node): ?string {
    if (!$node) {
      return sprintf('Node %s not found, skipped.', $item);
    }
    return NULL;
  }

  /**
   * Gather nodes by content type(s) and optional field condition.
   *
   * @param string|array $content_types
   *   Content type(s) to query.
   * @param string|null $field_name
   *   Optional field name to check for non-NULL values.
   * @param string $error_context
   *   Context string for error messages.
   *
   * @return array
   *   Array of node IDs.
   */
  protected function gatherNodesByType(string|array $content_types, ?string $field_name = NULL, string $error_context = 'nodes'): array {
    try {
      $query = $this->entityTypeManager->getStorage('node')
        ->getQuery()
        ->condition('type', $content_types, is_array($content_types) ? 'IN' : '=')
        ->accessCheck(FALSE);

      if ($field_name) {
        $query->condition($field_name, NULL, 'IS NOT NULL');
      }

      return $query->execute();
    }
    catch (\Exception $e) {
      $message = sprintf('Error gathering %s: %s', $error_context, $e->getMessage());
      $this->batchOpLog->appendError($message);
      return [];
    }
  }

  /**
   * Log success message for a processed node.
   *
   * @param int $nid
   *   The node ID.
   * @param string $node_title
   *   The node title.
   * @param string $success_message
   *   The success message.
   *
   * @return string
   *   Formatted success message.
   */
  protected function logSuccess(int $nid, string $node_title, string $success_message): string {
    $message = sprintf('Node %d (%s): %s', $nid, $node_title, $success_message);
    $this->batchOpLog->appendLog($message);
    return sprintf('Node %d processed successfully.', $nid);
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    try {
      $node = $this->loadNode($item);
      $error = $this->checkNodeExists($item, $node);
      if ($error) {
        return $error;
      }

      // Check for forward revision BEFORE processing, because after save the
      // current node will always be the latest revision. If we don't update
      // forward revisions, they will overwrite our changes when published.
      $forward_revision = get_forward_revision($node);

      // Process the default revision.
      $result = $this->processNode($node, $sandbox);
      $status_msg = $result;

      // If there's a forward revision, process it with the same migration
      // logic. This ensures that when the draft is published, it includes
      // our changes.
      if ($forward_revision) {
        $this->processNode($forward_revision, $sandbox);
        $sandbox['forward_revisions_count'] = (isset($sandbox['forward_revisions_count'])) ? ++$sandbox['forward_revisions_count'] : 1;
        $status_msg .= " Forward revision also updated.";
      }

      return $status_msg;
    }
    catch (\Exception $e) {
      return $this->handleProcessingError($item, $e);
    }
  }

  /**
   * Process a single node for migration.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node to process.
   * @param array $sandbox
   *   The sandbox array.
   *
   * @return string
   *   Result message.
   */
  abstract protected function processNode(Node $node, array &$sandbox): string;

  /**
   * Map terms from source field to destination field by name.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node.
   * @param string $source_field
   *   Source field name.
   * @param string $source_vocabulary
   *   Source vocabulary ID.
   * @param string $destination_field
   *   Destination field name.
   * @param string $destination_vocabulary
   *   Destination vocabulary ID.
   *
   * @return array
   *   Array with 'success' => bool, 'message' => string, 'terms_count' => int.
   */
  protected function mapTermsBetweenFields(
    Node $node,
    string $source_field,
    string $source_vocabulary,
    string $destination_field,
    string $destination_vocabulary,
  ): array {
    // Get source terms.
    $source_terms = get_node_field_terms($node, $source_field);
    if (empty($source_terms)) {
      return [
        'success' => FALSE,
        'message' => sprintf('Node %d (%s): No source terms found.', $node->id(), $node->getTitle()),
        'terms_count' => 0,
      ];
    }

    // Find corresponding terms in destination taxonomy.
    $destination_terms = [];
    foreach ($source_terms as $source_term) {
      if ($source_term->bundle() !== $source_vocabulary) {
        continue;
      }

      $term_name = $source_term->getName();
      $destination_term = find_term_by_name($destination_vocabulary, $term_name);

      if ($destination_term) {
        $destination_terms[] = $destination_term;
      }
      else {
        $this->batchOpLog->appendLog(sprintf('Term "%s" not found in destination vocabulary %s for node %d', $term_name, $destination_vocabulary, $node->id()));
      }
    }

    if (empty($destination_terms)) {
      return [
        'success' => FALSE,
        'message' => sprintf('Node %d (%s): No matching destination terms found.', $node->id(), $node->getTitle()),
        'terms_count' => 0,
      ];
    }

    // Add terms to destination field.
    $added = $this->addTermsToNodeField($node, $destination_field, $destination_terms);

    if (!$added) {
      return [
        'success' => FALSE,
        'message' => sprintf('Node %d (%s): Failed to add terms to destination field.', $node->id(), $node->getTitle()),
        'terms_count' => 0,
      ];
    }

    return [
      'success' => TRUE,
      'message' => '',
      'terms_count' => count($destination_terms),
    ];
  }

  /**
   * Get paragraphs from a node field.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node.
   * @param string $field_name
   *   The paragraph field name.
   *
   * @return \Drupal\paragraphs\ParagraphInterface[]
   *   Array of paragraph entities.
   */
  protected function getParagraphs(Node $node, string $field_name): array {
    $paragraphs = [];
    if ($node->hasField($field_name)) {
      foreach ($node->get($field_name)->referencedEntities() as $entity) {
        if ($entity instanceof ParagraphInterface) {
          $paragraphs[] = $entity;
        }
      }
    }
    return $paragraphs;
  }

  /**
   * Check if a term exists in a paragraph field.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph.
   * @param string $field_name
   *   The field name.
   * @param \Drupal\taxonomy\TermInterface $term
   *   The term to check.
   *
   * @return bool
   *   TRUE if term exists, FALSE otherwise.
   */
  protected function termExistsInParagraphField(ParagraphInterface $paragraph, string $field_name, TermInterface $term): bool {
    if (!$paragraph->hasField($field_name)) {
      return FALSE;
    }

    foreach ($paragraph->get($field_name)->referencedEntities() as $existing_term) {
      if ($existing_term->id() === $term->id()) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Add a term to a paragraph field if it doesn't already exist.
   *
   * This method modifies the paragraph and saves it (creating a new revision).
   * The caller is responsible for updating the parent node's field reference
   * to point to the new revision before saving the node.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph.
   * @param string $field_name
   *   The field name.
   * @param \Drupal\taxonomy\TermInterface $term
   *   The term to add.
   *
   * @return bool
   *   TRUE if term was added, FALSE if it already existed or field
   *   doesn't exist.
   */
  protected function addTermToParagraphField(ParagraphInterface $paragraph, string $field_name, TermInterface $term): bool {
    if (!$paragraph->hasField($field_name)) {
      return FALSE;
    }

    if ($this->termExistsInParagraphField($paragraph, $field_name, $term)) {
      return FALSE;
    }

    $field_values = $paragraph->get($field_name)->getValue();
    $field_values[] = ['target_id' => $term->id()];
    $paragraph->set($field_name, $field_values);

    // Save the paragraph to persist changes and create a new revision.
    $paragraph->save();

    return TRUE;
  }

  /**
   * Update a node's paragraph field to reference a paragraph's new revision.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node.
   * @param string $field_name
   *   The paragraph field name on the node.
   * @param int $delta
   *   The delta (index) of the field item to update.
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph whose new revision should be referenced.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  protected function updateParagraphFieldRevision(
    Node $node,
    string $field_name,
    int $delta,
    ParagraphInterface $paragraph,
  ): void {
    if (!$node->hasField($field_name)) {
      return;
    }

    $field = $node->get($field_name);
    $field_item = $field->get($delta);
    $field_item->setValue([
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ]);
  }

  /**
   * Create an Audience & Topics paragraph.
   *
   * @param string $paragraph_type
   *   The paragraph type (e.g., 'audience_topics').
   * @param array $initial_field_values
   *   Initial field values keyed by field name.
   *
   * @return \Drupal\paragraphs\ParagraphInterface
   *   The created paragraph.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createAudienceTopicsParagraph(string $paragraph_type, array $initial_field_values = []): ParagraphInterface {
    $paragraph_data = ['type' => $paragraph_type];
    foreach ($initial_field_values as $field_name => $values) {
      $paragraph_data[$field_name] = $values;
    }
    $paragraph = Paragraph::create($paragraph_data);
    $paragraph->save();
    return $paragraph;
  }

  /**
   * Add a paragraph to a node field.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node.
   * @param string $field_name
   *   The paragraph field name.
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph to add.
   */
  protected function addParagraphToNode(Node $node, string $field_name, ParagraphInterface $paragraph): void {
    $field_values = $node->get($field_name)->getValue();
    $field_values[] = [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];
    $node->set($field_name, $field_values);
  }

  /**
   * Check if a term exists in a node field by vocabulary and name.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node.
   * @param string $field_name
   *   The field name.
   * @param string $vocabulary
   *   The vocabulary ID.
   * @param string $term_name
   *   The term name to check.
   *
   * @return bool
   *   TRUE if term exists, FALSE otherwise.
   */
  protected function termExistsInField(Node $node, string $field_name, string $vocabulary, string $term_name): bool {
    $terms = get_node_field_terms($node, $field_name);
    foreach ($terms as $term) {
      if ($term->bundle() === $vocabulary && $term->getName() === $term_name) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Filter terms by vocabulary.
   *
   * @param array $terms
   *   Array of term entities.
   * @param string $vocabulary
   *   The vocabulary ID.
   *
   * @return \Drupal\taxonomy\TermInterface[]
   *   Filtered array of terms.
   */
  protected function getTermsByVocabulary(array $terms, string $vocabulary): array {
    $filtered = [];
    foreach ($terms as $term) {
      if ($term instanceof TermInterface && $term->bundle() === $vocabulary) {
        $filtered[] = $term;
      }
    }
    return $filtered;
  }

}
