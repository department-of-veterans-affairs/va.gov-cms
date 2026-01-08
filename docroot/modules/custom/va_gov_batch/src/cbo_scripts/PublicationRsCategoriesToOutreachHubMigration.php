<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\node\Entity\Node;
use Drupal\va_gov_resources_and_support\Service\RsTagMigrationService;

/**
 * Migration: Publication content type - R&S Categories → Outreach Hub taxonomy.
 *
 * Migrates tags from field_lc_categories (R&S Categories taxonomy) to
 * a field referencing Outreach Hub taxonomy (outreach_materials_topics).
 *
 * @code
 * drush codit-batch-operations:run
 *   PublicationRsCategoriesToOutreachHubMigration
 * @endcode
 */
class PublicationRsCategoriesToOutreachHubMigration extends BaseRsTagMigration {

  /**
   * Source field name (R&S Categories).
   */
  const SOURCE_FIELD = 'field_lc_categories';

  /**
   * Source taxonomy vocabulary ID (R&S Categories).
   */
  const SOURCE_VOCABULARY = 'lc_categories';

  /**
   * Destination field name (Outreach Hub).
   */
  const DESTINATION_FIELD = 'field_outreach_materials_topics';

  /**
   * Destination taxonomy vocabulary ID (Outreach Hub).
   */
  const DESTINATION_VOCABULARY = 'outreach_materials_topics';

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return 'Publication: R&S Categories → Outreach Hub Taxonomy';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return 'Migrates tags from R&S Categories taxonomy to Outreach Hub taxonomy for Publication content type.';
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@completed out of @total Publication nodes processed.';
  }

  /**
   * {@inheritdoc}
   */
  public function getItemType(): string {
    return 'outreach_asset node';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    return $this->gatherNodesByType('outreach_asset', self::SOURCE_FIELD, 'outreach_asset nodes');
  }

  /**
   * {@inheritdoc}
   */
  protected function getFieldValidations(): array {
    return [
      [
        'entity_type' => 'node',
        'bundle' => 'outreach_asset',
        'field_name' => self::SOURCE_FIELD,
        'expected_vocabulary' => self::SOURCE_VOCABULARY,
        'field_label' => 'R&S Categories (source)',
      ],
      [
        'entity_type' => 'node',
        'bundle' => 'outreach_asset',
        'field_name' => self::DESTINATION_FIELD,
        'expected_vocabulary' => self::DESTINATION_VOCABULARY,
        'field_label' => 'Outreach Materials Topics (destination)',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function processNode(
    Node $node,
    array $node_info,
    RsTagMigrationService $migration_service,
    array &$sandbox,
  ): string {
    $result = $this->mapTermsBetweenFields(
      $node,
      $migration_service,
      self::SOURCE_FIELD,
      self::SOURCE_VOCABULARY,
      self::DESTINATION_FIELD,
      self::DESTINATION_VOCABULARY,
      $node_info
    );

    if (!$result['success']) {
      return $result['message'];
    }

    $log_message = sprintf(
      'R&S tag migration: Copied %d term(s) from %s to %s',
      $result['terms_count'],
      self::SOURCE_FIELD,
      self::DESTINATION_FIELD
    );
    $this->saveNodeRevision($node, $log_message);

    return $this->logSuccess(
      $node_info['nid'],
      $node_info['title'],
      'Successfully migrated ' . $result['terms_count'] . ' term(s).'
    );
  }

}
