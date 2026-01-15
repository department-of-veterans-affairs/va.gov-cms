<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\node\Entity\Node;

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
  protected function processNode(Node $node, array &$sandbox): string {
    $result = $this->mapTermsBetweenFields(
      $node,
      self::SOURCE_FIELD,
      self::SOURCE_VOCABULARY,
      self::DESTINATION_FIELD,
      self::DESTINATION_VOCABULARY
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
      $node->id(),
      $node->getTitle(),
      sprintf('Successfully migrated %d term(s).', $result['terms_count'])
    );
  }

}
