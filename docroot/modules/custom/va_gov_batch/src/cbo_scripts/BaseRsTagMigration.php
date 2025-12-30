<?php

namespace Drupal\va_gov_batch\cbo_scripts;

require_once __DIR__ . '/../../../../../../scripts/content/script-library.php';

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\va_gov_resources_and_support\Service\RsTagMigrationService;

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
   * Get the migration service.
   *
   * @return \Drupal\va_gov_resources_and_support\Service\RsTagMigrationService
   *   The migration service.
   */
  protected function getMigrationService(): RsTagMigrationService {
    return \Drupal::service('va_gov_resources_and_support.rs_tag_migration');
  }

  /**
   * Load a node and return it, or NULL if not found.
   *
   * @param int $nid
   *   The node ID.
   *
   * @return \Drupal\node\Entity\Node|null
   *   The loaded node, or NULL if not found.
   */
  protected function loadNode(int $nid): ?Node {
    return Node::load($nid);
  }

  /**
   * Get node ID and title for logging.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node.
   *
   * @return array
   *   Array with 'nid' and 'title' keys.
   */
  protected function getNodeInfo(Node $node): array {
    return [
      'nid' => $node->id(),
      'title' => $node->getTitle(),
    ];
  }

  /**
   * Save node revision with logging, preserving moderation state.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to save.
   * @param string $message
   *   The log message.
   * @param bool $new
   *   TRUE if the revision is new, FALSE to overwrite the latest.
   *
   * @return int
   *   The revision ID.
   */
  public function saveNodeRevision(NodeInterface $node, string $message = '', $new = TRUE): int {
    return save_node_revision($node, $message, $new);
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
    $message = "Error processing node ID $item: " . $e->getMessage();
    $this->batchOpLog->appendError($message);
    return "Error processing node $item.";
  }

  /**
   * Check if node exists, return error message if not.
   *
   * @param mixed $item
   *   The item being processed (usually node ID).
   * @param \Drupal\node\Entity\Node|null $node
   *   The loaded node.
   *
   * @return string|null
   *   Error message if node not found, NULL otherwise.
   */
  protected function checkNodeExists(mixed $item, ?Node $node): ?string {
    if (!$node) {
      return "Node $item not found, skipped.";
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
  protected function gatherNodesByType(
    string|array $content_types,
    ?string $field_name = NULL,
    string $error_context = 'nodes',
  ): array {
    $items = [];
    try {
      $query = $this->entityTypeManager->getStorage('node')
        ->getQuery()
        ->condition('type', $content_types, is_array($content_types) ? 'IN' : '=')
        ->accessCheck(FALSE);

      if ($field_name) {
        $query->condition($field_name, NULL, 'IS NOT NULL');
      }

      $nids = $query->execute();

      foreach ($nids as $nid) {
        $items[] = $nid;
      }
    }
    catch (\Exception $e) {
      $message = "Error gathering $error_context: " . $e->getMessage();
      $this->batchOpLog->appendError($message);
    }

    return $items;
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
    $message = "Node $nid ($node_title): $success_message";
    $this->batchOpLog->appendLog($message);
    return "Node $nid processed successfully.";
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

      $node_info = $this->getNodeInfo($node);
      $migration_service = $this->getMigrationService();

      return $this->processNode($node, $node_info, $migration_service, $sandbox);
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
   * @param array $node_info
   *   Node info array with 'nid' and 'title' keys.
   * @param \Drupal\va_gov_resources_and_support\Service\RsTagMigrationService $migration_service
   *   The migration service.
   * @param array $sandbox
   *   The sandbox array.
   *
   * @return string
   *   Result message.
   */
  abstract protected function processNode(
    Node $node,
    array $node_info,
    RsTagMigrationService $migration_service,
    array &$sandbox,
  ): string;

  /**
   * Map terms from source field to destination field by name.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node.
   * @param \Drupal\va_gov_resources_and_support\Service\RsTagMigrationService $migration_service
   *   The migration service.
   * @param string $source_field
   *   Source field name.
   * @param string $source_vocabulary
   *   Source vocabulary ID.
   * @param string $destination_field
   *   Destination field name.
   * @param string $destination_vocabulary
   *   Destination vocabulary ID.
   * @param array $node_info
   *   Node info array with 'nid' and 'title' keys.
   *
   * @return array
   *   Array with 'success' => bool, 'message' => string, 'terms_count' => int.
   */
  protected function mapTermsBetweenFields(
    Node $node,
    RsTagMigrationService $migration_service,
    string $source_field,
    string $source_vocabulary,
    string $destination_field,
    string $destination_vocabulary,
    array $node_info,
  ): array {
    // Get source terms.
    $source_terms = $migration_service->getNodeFieldTerms($node, $source_field);
    if (empty($source_terms)) {
      return [
        'success' => FALSE,
        'message' => "Node {$node_info['nid']} ({$node_info['title']}): No source terms found.",
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
      $destination_term = $migration_service->findTermByName(
        $destination_vocabulary,
        $term_name
      );

      if ($destination_term) {
        $destination_terms[] = $destination_term;
      }
      else {
        $migration_service->logWarning('Term "@term" not found in destination vocabulary @vocab for node @nid', [
          '@term' => $term_name,
          '@vocab' => $destination_vocabulary,
          '@nid' => $node_info['nid'],
        ]);
      }
    }

    if (empty($destination_terms)) {
      return [
        'success' => FALSE,
        'message' => "Node {$node_info['nid']} ({$node_info['title']}): No matching destination terms found.",
        'terms_count' => 0,
      ];
    }

    // Add terms to destination field.
    $added = $migration_service->addTermsToNodeField(
      $node,
      $destination_field,
      $destination_terms
    );

    if (!$added) {
      return [
        'success' => FALSE,
        'message' => "Node {$node_info['nid']} ({$node_info['title']}): Failed to add terms to destination field.",
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
  protected function termExistsInParagraphField(
    ParagraphInterface $paragraph,
    string $field_name,
    TermInterface $term,
  ): bool {
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
  protected function addTermToParagraphField(
    ParagraphInterface $paragraph,
    string $field_name,
    TermInterface $term,
  ): bool {
    if (!$paragraph->hasField($field_name)) {
      return FALSE;
    }

    if ($this->termExistsInParagraphField($paragraph, $field_name, $term)) {
      return FALSE;
    }

    $field_values = $paragraph->get($field_name)->getValue();
    $field_values[] = ['target_id' => $term->id()];
    $paragraph->set($field_name, $field_values);
    $paragraph->save();

    return TRUE;
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
   */
  protected function createAudienceTopicsParagraph(
    string $paragraph_type,
    array $initial_field_values = [],
  ): ParagraphInterface {
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
  protected function addParagraphToNode(
    Node $node,
    string $field_name,
    ParagraphInterface $paragraph,
  ): void {
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
  protected function termExistsInField(
    Node $node,
    string $field_name,
    string $vocabulary,
    string $term_name,
  ): bool {
    $terms = $this->getMigrationService()->getNodeFieldTerms($node, $field_name);
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
