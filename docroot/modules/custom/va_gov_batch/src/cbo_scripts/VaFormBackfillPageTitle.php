<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;

/**
 * Backfills empty field_va_form_page_title on VA Form nodes.
 *
 * The Forms DB migration seeds this field on first import only; it is not in
 * overwrite_properties, so existing nodes keep an empty Page title until this
 * runs. Values match the migration: page_title_prefix + displayName (node title
 * still uses title_prefix + displayName). field_va_form_number stands in for
 * CSV displayName as the migration maps field_va_form_number:displayName.
 *
 * To run manually:
 * drush codit-batch-operations:run VaFormBackfillPageTitle
 *
 * @see https://github.com/department-of-veterans-affairs/va.gov-cms/issues/23674
 */
class VaFormBackfillPageTitle extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return 'Backfill VA Form Page title (field_va_form_page_title)';
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return '@total VA Form nodes were processed. @completed had Page title backfilled (empty field only).';
  }

  /**
   * Gets page_title_prefix from va_node_form migration config.
   */
  protected function getPageTitlePrefix(): string {
    $config = $this->configFactory->get('migrate_plus.migration.va_node_form');
    $prefix = $config->get('source.constants.page_title_prefix');
    return is_string($prefix) && $prefix !== '' ? $prefix : 'VA Form ';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    $items = [];
    try {
      $definitions = $this->entityFieldManager->getFieldDefinitions('node', 'va_form');
      if (!isset($definitions['field_va_form_page_title'])) {
        $this->batchOpLog->appendLog('field_va_form_page_title is not defined on va_form; nothing to do.');
        return [];
      }

      $storage = $this->entityTypeManager->getStorage('node');
      $query = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'va_form');

      $empty_page_title = $query->orConditionGroup()
        ->condition('field_va_form_page_title', NULL, 'IS NULL')
        ->condition('field_va_form_page_title', '', '=');
      $query->condition($empty_page_title);
      $query->exists('field_va_form_number');

      $items = $query->execute() ?: [];
    }
    catch (\Exception $e) {
      $message = 'Error gathering VA Form nodes: ' . $e->getMessage();
      $this->batchOpLog->appendError($message);
    }

    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    try {
      $storage = $this->entityTypeManager->getStorage('node');
      /** @var \Drupal\node\Entity\Node $node */
      $node = $storage->load($item);
      if (!$node || $node->bundle() !== 'va_form') {
        return "Skipped missing node $item.";
      }

      if (!$node->get('field_va_form_page_title')->isEmpty()) {
        return "VA Form node $item already has Page title; skipped.";
      }

      $form_number = $node->get('field_va_form_number')->value;
      if ($form_number === NULL || $form_number === '') {
        return "VA Form node $item has no form number; skipped.";
      }

      $prefix = $this->getPageTitlePrefix();
      $node->set('field_va_form_page_title', $prefix . $form_number);
      $revision_message = 'VACMS-23674: Backfill Page title from migration prefix + form number.';
      $this->saveNodeRevision($node, $revision_message);

      return "VA Form node $item was processed.";
    }
    catch (\Exception $e) {
      $message = "Error processing VA Form node $item: " . $e->getMessage();
      $this->batchOpLog->appendError($message);

      return $message;
    }
  }

}
