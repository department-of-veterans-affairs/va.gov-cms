<?php

namespace Drupal\va_gov_migrate\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\migrate_plus\DataFetcherPluginManager;
use Drupal\node\NodeInterface;
use Drupal\va_gov_workflow\Service\Flagger;
use League\Csv\Reader;
use League\Csv\Statement;
use Psr\Log\LoggerInterface;

/**
 * Service to archive IntranetOnly forms in the CMS.
 */
class ArchiveIntranetOnlyFormsService {

  /**
   * EntityTypemanager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Migrate plus datafetcher plugin manager.
   *
   * @var \Drupal\migrate_plus\DataFetcherPluginManager
   */
  protected $dataFetcherPluginManager;

  /**
   * The logger channel for va_gov_migrate.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $migrateChannelLogger;

  /**
   * Workflow flagger service.
   *
   * @var \Drupal\va_gov_workflow\Service\Flagger
   */
  protected $flagger;

  /**
   * The VA gov NotificationsManager.
   *
   * @var \Drupal\va_gov_notifications\Service\NotificationsManager
   */
  protected $notificationsManager;

  /**
   * Constructor for this set of drush commands.
   *
   * @param \Drupal\Core\Database\Connection $data_base
   *   Core database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\va_gov_workflow\Service\Flagger $flaggerservice
   *   Workflow flagger service.
   * @param \Drupal\migrate_plus\DataFetcherPluginManager $data_fetcher_plugin_manager
   *   DataFetcherPluginManager.
   * @param \Psr\Log\LoggerInterface $migrate_channel_logger
   *   LoggerChannel for va_gov_migrate.
   */
  public function __construct(
    Connection $data_base,
    EntityTypeManagerInterface $entity_type_manager,
    Flagger $flaggerservice,
    DataFetcherPluginManager $data_fetcher_plugin_manager,
    LoggerInterface $migrate_channel_logger,
  ) {
    $this->database = $data_base;
    $this->entityTypeManager = $entity_type_manager;
    $this->flagger = $flaggerservice;
    $this->dataFetcherPluginManager = $data_fetcher_plugin_manager;
    $this->migrateChannelLogger = $migrate_channel_logger;
  }

  /**
   * Archive IntranetOnly forms in the CMS.
   *
   * @command va_gov_migrate:archive-intranet-only-forms
   * @aliases va-gov-archive-intranet-only-forms
   *
   * @throws \League\Csv\UnavailableStream
   *   Thrown when the file is not available.
   * @throws \League\Csv\Exception
   *   Thrown if the offset is a negative integer.
   * @throws \League\Csv\InvalidArgument
   *   Thrown by enclosure or delimiter arguments are more than one character.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function archiveIntranetOnlyForms() {
    $this->migrateChannelLogger->info('Archiving intranet-only forms...');

    $csv = Reader::createFromPath(DRUPAL_ROOT . '/sites/default/files/migrate_source/va_forms_data.csv', 'r');
    $csv->setHeaderOffset(0);
    $csv->setEnclosure('"');
    $csv->setDelimiter(',');

    // Create a Statement.
    $stmt = (new Statement())
      ->select('rowid')
    // Start from first record.
      ->offset(0)
      ->limit(-1)
      ->andWhere('IntranetOnly', '=', '1')
    // Get all records.
      ->orderByAsc('rowid');

    // Process the CSV with the statement and filter for IntranetOnly = 1.
    $records = $stmt->process($csv);

    $intranet_only = [];
    foreach ($records as $record) {
      // Make an array of the rowids.
      $intranet_only[] = $record['rowid'];
    }
    // Get all the non-archived forms in the CMS that are IntranetOnly.
    $select = $this->database->select('node__field_va_form_row_id', 'nfvfri');
    $select->join('content_moderation_state_field_data', 'cmsfd', 'nfvfri.entity_id = cmsfd.content_entity_id');
    $select->fields('nfvfri', ['entity_id'])
      ->condition('nfvfri.field_va_form_row_id_value', $intranet_only, 'IN')
      ->condition('cmsfd.moderation_state', 'archived', '<>');
    $nids = $select->execute()->fetchCol();

    $forms_to_archive = $this->entityTypeManager->getStorage('node')->loadMultiple(array_values($nids));
    $message = 'Archived due to being set IntranetOnly in Forms CSV.';

    // Archive the nodes.
    foreach ($forms_to_archive as $form_to_archive) {
      $this->archiveNode($form_to_archive, $message);
    }
  }

  /**
   * Archive a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to archive.
   * @param string $message
   *   The revision message.
   */
  protected function archiveNode(NodeInterface $node, string $message) {
    $node->set('moderation_state', 'archived');
    $node->setRevisionLogMessage($message);
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
  }

}
