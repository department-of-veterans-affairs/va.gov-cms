<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\node\Entity\Node;
use Drupal\va_gov_resources_and_support\Service\RsTagMigrationService;

/**
 * Migration: CLP and VA Benefits taxonomy migrations.
 *
 * Placeholder migration for CLP and VA Benefits taxonomy migrations.
 * Specific requirements to be determined based on use cases.
 *
 * To run: drush codit-batch-operations:run ClpVaBenefitsMigration
 */
class ClpVaBenefitsMigration extends BaseRsTagMigration {

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return 'CLP and VA Benefits Taxonomy Migrations';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return 'Migration for CLP content type and VA Benefits taxonomy. Specific use cases to be implemented.';
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@completed out of @total CLP nodes processed.';
  }

  /**
   * {@inheritdoc}
   */
  public function getItemType(): string {
    return 'campaign_landing_page node';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    return $this->gatherNodesByType('campaign_landing_page', NULL, 'CLP nodes');
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
    // Placeholder implementation.
    // Specific migration logic to be added based on requirements.
    $migration_service->logInfo('CLP/VA Benefits migration: Node @nid (@title) - Placeholder', [
      '@nid' => $node_info['nid'],
      '@title' => $node_info['title'],
    ]);

    return "Node {$node_info['nid']} ({$node_info['title']}): CLP/VA Benefits migration placeholder - no action taken.";
  }

}
