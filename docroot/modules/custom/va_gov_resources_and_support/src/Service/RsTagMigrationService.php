<?php

namespace Drupal\va_gov_resources_and_support\Service;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Service for migrating R&S taxonomy tags.
 */
class RsTagMigrationService {

  /**
   * The CMS Migrator user ID.
   */
  const CMS_MIGRATOR_ID = 1317;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected EntityFieldManagerInterface $entityFieldManager;

  /**
   * Constructs a RsTagMigrationService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_factory,
    EntityFieldManagerInterface $entity_field_manager,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerFactory = $logger_factory;
    $this->entityFieldManager = $entity_field_manager;
  }

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
  public function getFieldDefinition(string $entity_type, string $bundle, string $field_name): ?FieldDefinitionInterface {
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
  public function getFieldCardinality(string $entity_type, string $bundle, string $field_name): int {
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
  public function getFieldTaxonomyVocabulary(string $entity_type, string $bundle, string $field_name): ?string {
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
        $this->loggerFactory->get('va_gov_resources_and_support')
          ->warning('Field @field on @entity_type:@bundle has multiple target vocabularies (@vocabularies). Returning only the first: @first', [
            '@field' => $field_name,
            '@entity_type' => $entity_type,
            '@bundle' => $bundle,
            '@vocabularies' => $all_vocabularies,
            '@first' => $first_vocabulary,
          ]);
        return $first_vocabulary;
      }
    }

    // For views-based handlers, we need to check the view configuration.
    // This is a simplified approach - in practice, you might need to
    // load the view and check its configuration.
    return NULL;
  }

  /**
   * Find a taxonomy term by name in a vocabulary.
   *
   * @param string $vocabulary_id
   *   The vocabulary ID.
   * @param string $term_name
   *   The term name.
   *
   * @return \Drupal\taxonomy\TermInterface|null
   *   The term or NULL if not found.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function findTermByName(string $vocabulary_id, string $term_name): ?TermInterface {
    $terms = $this->entityTypeManager
      ->getStorage('taxonomy_term')
      ->loadByProperties([
        'vid' => $vocabulary_id,
        'name' => $term_name,
      ]);

    return !empty($terms) ? reset($terms) : NULL;
  }

  /**
   * Get all terms from a node field.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   * @param string $field_name
   *   The field name.
   *
   * @return \Drupal\taxonomy\TermInterface[]
   *   Array of terms.
   */
  public function getNodeFieldTerms(NodeInterface $node, string $field_name): array {
    $terms = [];
    if ($node->hasField($field_name)) {
      foreach ($node->get($field_name)->referencedEntities() as $term) {
        if ($term instanceof TermInterface) {
          $terms[] = $term;
        }
      }
    }
    return $terms;
  }

  /**
   * Add terms to a node field (additive, not overwriting).
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   * @param string $field_name
   *   The field name.
   * @param \Drupal\taxonomy\TermInterface[] $terms_to_add
   *   Terms to add.
   *
   * @return bool
   *   TRUE if terms were added, FALSE otherwise.
   */
  public function addTermsToNodeField(NodeInterface $node, string $field_name, array $terms_to_add): bool {
    if (!$node->hasField($field_name)) {
      $this->loggerFactory->get('va_gov_resources_and_support')
        ->error('Field @field does not exist on node @nid', [
          '@field' => $field_name,
          '@nid' => $node->id(),
        ]);
      return FALSE;
    }

    $existing_terms = $this->getNodeFieldTerms($node, $field_name);
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
        $this->loggerFactory->get('va_gov_resources_and_support')
          ->warning('Field @field on node @nid is at cardinality limit (@limit). Cannot add @count new term(s). Excluded terms: @terms', [
            '@field' => $field_name,
            '@nid' => $node->id(),
            '@limit' => $cardinality,
            '@count' => $terms_to_add_count,
            '@terms' => implode(', ', array_map(function (TermInterface $term) {
              return $term->getName();
            }, $terms_excluded)),
          ]);
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
      $this->loggerFactory->get('va_gov_resources_and_support')
        ->warning('Field @field on node @nid: Added @added of @total new term(s) due to cardinality limit (@limit). Excluded @excluded term(s): @terms', [
          '@field' => $field_name,
          '@nid' => $node->id(),
          '@added' => count($terms_added),
          '@total' => $terms_to_add_count,
          '@limit' => $cardinality,
          '@excluded' => count($terms_excluded),
          '@terms' => implode(', ', array_map(function (TermInterface $term) {
            return $term->getName();
          }, $terms_excluded)),
        ]);
    }

    $node->set($field_name, $field_values);
    return TRUE;
  }

  /**
   * Log an error message.
   *
   * @param string $message
   *   The error message.
   * @param array $context
   *   Context variables.
   */
  public function logError(string $message, array $context = []): void {
    $this->loggerFactory->get('va_gov_resources_and_support')->error($message, $context);
  }

  /**
   * Log a warning message.
   *
   * @param string $message
   *   The warning message.
   * @param array $context
   *   Context variables.
   */
  public function logWarning(string $message, array $context = []): void {
    $this->loggerFactory->get('va_gov_resources_and_support')->warning($message, $context);
  }

  /**
   * Log an info message.
   *
   * @param string $message
   *   The info message.
   * @param array $context
   *   Context variables.
   */
  public function logInfo(string $message, array $context = []): void {
    $this->loggerFactory->get('va_gov_resources_and_support')->info($message, $context);
  }

}
