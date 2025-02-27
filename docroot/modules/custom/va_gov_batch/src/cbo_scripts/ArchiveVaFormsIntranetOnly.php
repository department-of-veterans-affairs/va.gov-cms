<?php

namespace Drupal\va_gov_batch\cbo_scripts;

require 'vendor/autoload.php';

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use League\Csv\Reader;
use League\Csv\Statement;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @file
 * For dual numbers in the phone_number paragraph extension field.
 *
 * For VACMS-20371.
 * This file should be run SECOND, after you run
 * drush codit-batch-operations:run RemoveNonNumericalCharactersFromExtensions .
 * Then, run this file
 * drush codit-batch-operations:run SplitExtensionWithTwoNumbers .
 */
/**
 * Split extensions with two numbers.
 */
class ArchiveVaFormsIntranetOnly extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return <<<TITLE
    For:
      - VACMS-20371: https://github.com/department-of-veterans-affairs/va.gov-cms/issues/20371
      - Splitting extensions when there are two
    TITLE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription():string {
    return <<<ENDHERE
    Splits extensions when there are two by keeping the first and
    using the second to create a new phone paragraph
    ENDHERE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return 'Phone extensions have been processed with a total @total processed and @completed completed.';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    $csv = Reader::createFromPath(DRUPAL_ROOT . '/sites/default/files/migrate_source/va_forms_data.csv', 'r');
    $csv->setHeaderOffset(0);
    $csv->setEnclosure('"');
    $csv->setDelimiter(',');

    // Create a Statement
    $stmt = (new Statement())
      ->select('rowid')
      ->offset(0)     // Start from first record
      ->limit(-1)
      ->andWhere('IntranetOnly', '=', '1')
      ->orderByAsc('rowid');  // Get all records

    // Process the CSV with the statement and filter for IntranetOnly = 1
    $records = $stmt->process($csv);

    $intranet_only = [];
    foreach ($records as $record) {
      // Make an array of the rowids
      $intranet_only[] = $record['rowid'];
    }
    // $database = \Drupal::database();
    $select = \Drupal::database()->select('node__field_va_form_row_id', 'nfvfri');
    $select->join('content_moderation_state_field_data', 'cmsfd', 'nfvfri.entity_id = cmsfd.content_entity_id');
    $select->fields('nfvfri', ['entity_id'])
      ->condition('nfvfri.field_va_form_row_id_value', $intranet_only, 'IN')
      ->condition('cmsfd.moderation_state', 'archived' , '<>' );
    $nids = $select->execute()->fetchCol();

    return $nids;
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    $message = '';
    $node = Node::load($item);
    $node->set('moderation_state', 'archived');
    $node->setRevisionLogMessage('Archived due to being set IntranetOnly in Forms CSV.');
    $node->setNewRevision(TRUE);
    $node->setUnpublished();
    // Assign to CMS Migrator user.
    $node->setRevisionUserId(1317);
    // Prevents some other actions.
    $node->setSyncing(TRUE);
    $node->setChangedTime(time());
    $node->isDefaultRevision(TRUE);
    $node->setRevisionCreationTime(time());
    $node->save();
    return "Archived node $item because it is now an IntranetOnly form.";
  }


}
