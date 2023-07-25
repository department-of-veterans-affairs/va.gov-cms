<?php

namespace Drupal\va_gov_migrate\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\migrate_plus\DataFetcherPluginManager;
use Drupal\migrate_plus\Entity\Migration;
use Drupal\node\NodeInterface;
use Drupal\va_gov_facilities\FacilityOps;
use Drupal\va_gov_notifications\Service\NotificationsManager;
use Drupal\va_gov_workflow\Service\Flagger;
use Drush\Commands\DrushCommands;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Drush commands related to migrations.
 */
class Commands extends DrushCommands {

  // The UID of the CMS Help Desk account subscribing to facility messages.
  const USER_CMS_HELP_DESK_NOTIFICATIONS = 4050;

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
   * @param \Drupal\va_gov_notifications\Service\NotificationsManager $notifications_manager
   *   VA gov NotificationsManager service.
   */
  public function __construct(
    Connection $data_base,
    EntityTypeManagerInterface $entity_type_manager,
    Flagger $flaggerservice,
    DataFetcherPluginManager $data_fetcher_plugin_manager,
    LoggerInterface $migrate_channel_logger,
    NotificationsManager $notifications_manager
  ) {
    parent::__construct();
    $this->database = $data_base;
    $this->entityTypeManager = $entity_type_manager;
    $this->flagger = $flaggerservice;
    $this->dataFetcherPluginManager = $data_fetcher_plugin_manager;
    $this->migrateChannelLogger = $migrate_channel_logger;
    $this->notificationsManager = $notifications_manager;
  }

  /**
   * Clean up bad node revisions.
   *
   * @command va:gov-clean-revs
   * @aliases vg-cr,va-gov-clean-revs
   */
  public function cleanRevs() {
    $time = 1572551719;

    $query = $this->database->select('node_revision', 'nr');
    $query->condition('revision_timestamp', $time);
    $query->fields('nr', ['vid']);
    $vids = $query->execute()->fetchCol();

    $query = $this->database->select('node_revision', 'nr');
    $query->condition('revision_log', 'Update of status by migration.');
    $query->condition('nid', 1884);
    $query->fields('nr', ['vid']);
    $more_vids = $query->execute()->fetchCol();

    $vids = array_merge($vids, $more_vids);
    $this->migrateChannelLogger->info('Attempting to Delete ' . count($vids) . ' node revisions');
    $missed_vids = [];
    foreach ($vids as $vid) {
      try {
        $this->migrateChannelLogger->info('Deleting: ' . $vid);
        $this->entityTypeManager->getStorage('node')->deleteRevision($vid);
      }
      catch (EntityStorageException $e) {
        $this->migrateChannelLogger->warning('Vid ' . $vid . ' could not be deleted: ' . $e->getMessage());
        $missed_vids[] = $vid;
      }
    }

    $count = count($vids) - count($missed_vids);
    // @phpstan-ignore-next-line
    $this->logger->success("Deleted {$count} revision(s).");
    $this->logger->warning('The following revisions were not deleted: ' . implode(', ', $missed_vids));
  }

  /**
   * Flag any facilities that no longer exist in Facilty API.
   *
   * @command va_gov_migrate:flag-missing-facilities
   * @aliases va-gov-flag-missing-facilities
   */
  public function flagMissingFacilities() {
    $facilities_in_fapi = $this->getFapiList();
    $count_fapi = count($facilities_in_fapi);
    // Make sure facilities in fapi makes sense or we will flag all facilities.
    if ($this->facilityCountSeemsFaulty($count_fapi)) {
      $vars = [
        '%count' => $count_fapi,
      ];

      $this->migrateChannelLogger->log(LogLevel::WARNING, 'The facility API returned %count facilities, which seems suspicious. Flagging aborted.', $vars);
      return;
    }
    $facilities_to_flag = $this->getFacilitiesToFlag($facilities_in_fapi);
    $count_flagged = count($facilities_to_flag);
    $count_archived = 0;
    if ($count_flagged) {
      $facility_nodes_to_flag = $this->entityTypeManager->getStorage('node')->loadMultiple(array_values($facilities_to_flag));
      foreach ($facility_nodes_to_flag as $facility_node_to_flag) {
        if (!FacilityOps::isAutoArchiveFacility($facility_node_to_flag)) {
          $this->addNodeRevision($facility_node_to_flag);
          $this->flagger->setFlag('removed_from_source', $facility_node_to_flag);
          // Send email to CMS Help Desk for follow-up steps.
          $message_fields = $this->notificationsManager->buildMessageFields($facility_node_to_flag, 'Facility removed:');
          $this->notificationsManager->send('va_facility_removed_from_source', self::USER_CMS_HELP_DESK_NOTIFICATIONS, $message_fields);
          // Log amount to be processed.
        }
        else {
          $this->archiveRemovedFacility($facility_node_to_flag);
          $count_flagged--;
          $count_archived++;
        }
      }
      $vars = [
        '%count_flagged' => $count_flagged,
        '%count_archived' => $count_archived,
      ];

      if ($vars['%count_flagged'] > 0) {
        $msg = 'Flagged %count_flagged facilities as removed from Facility API.';
        $this->migrateChannelLogger->log(LogLevel::INFO, $msg, $vars);
        // Create drush output.
        // @phpstan-ignore-next-line
        $this->logger->success("Flagged {$count_flagged} facilities as removed from Facility API.");
      }
      if ($vars['%count_archived'] > 0) {
        $msg = 'Archived %count_archived facilities due to removal from Facility API.';
        $this->migrateChannelLogger->log(LogLevel::INFO, $msg, $vars);
        // Create drush output.
        // @phpstan-ignore-next-line
        $this->logger->success("Archived {$count_archived} facilities due to removal from Facility API.");
      }
    }
  }

  /**
   * Archive a facility.
   *
   * @param \Drupal\node\NodeInterface $facility
   *   The facility to archive.
   */
  protected function archiveRemovedFacility(NodeInterface $facility) {
    $this->clearStatusData($facility);
    $facility->set('moderation_state', 'archived');
    $facility->setRevisionLogMessage('Archived due to removal from Facility API.');
    $facility->setNewRevision(TRUE);
    $facility->setUnpublished();
    // Assign to CMS Migrator user.
    $facility->setRevisionUserId(1317);
    // Prevents some other actions.
    $facility->setSyncing(TRUE);
    $facility->setChangedTime(time());
    $facility->isDefaultRevision(TRUE);
    $facility->setRevisionCreationTime(time());
    $facility->save();
  }

  /**
   * Clear out a facility's status data.
   *
   * @param \Drupal\node\NodeInterface $facility
   *   The facility to clean.
   */
  protected function clearStatusData(NodeInterface &$facility) {
    if ($facility->hasField('field_operating_status_facility')) {
      $facility->field_operating_status_facility->value = 'closed';
    }
    if ($facility->hasField('field_operating_status_more_info')) {
      $facility->field_operating_status_more_info->value = '';
    }
    $facility->save();
  }

  /**
   * Add a revision to a node to log what happened.
   *
   * @param \Drupal\node\NodeInterface $facility
   *   The facility node to be updated.
   */
  protected function addnodeRevision(NodeInterface $facility): void {
    $facility->setRevisionCreationTime(time());
    $facility->setChangedTime(time());
    $msg = "No longer appears in the Facility API.";
    $facility->setRevisionLogMessage($msg);
    // New revision will inherit content moderation status from default rev.
    $facility->setNewRevision(TRUE);
    $facility->isDefaultRevision(TRUE);
    // Setting revision as CMS migrator.
    $facility->setRevisionUserId(1317);
    $facility->save();
  }

  /**
   * Get array of facilities that need flagging for removal.
   *
   * @param array $facilities_in_fapi
   *   An array of facility api ids as keys.
   *
   * @return array
   *   Array of facility_api-id => nid pairs that need flagging.
   */
  protected function getFacilitiesToFlag(array $facilities_in_fapi): array {
    // Remove any facilities that are in the API.
    $missing_facilities = array_diff_key($this->getFacilitiesInCms(), $facilities_in_fapi);
    // Remove any facilities that are already flagged.
    $facilities_to_flag = array_diff($missing_facilities, $this->getFlaggedRemovedFromSource());
    // Remove any facilities that are already archived.
    $facilities_to_flag = array_diff($facilities_to_flag, $this->getArchivedFacilities());

    return $facilities_to_flag;
  }

  /**
   * Get all facilities present from the facility API.
   *
   * @return array
   *   Array of facility id keys for all facilities in FAPI.
   */
  protected function getFapiList(): array {
    // Will end up keyed <fapi_id> => <n>.
    $facilities = [];
    // Use settings from facility migration to get data.
    /** @var \Drupal\migrate_plus\Entity\MigrationInterface $facility_migration */
    $facility_migration = Migration::load('va_node_health_care_local_facility');
    if ($facility_migration) {
      $source = $facility_migration->get('source');
      $url = $source['urls'][0];
      $headers = $source['headers'];
      $fetcher = $this->dataFetcherPluginManager->createInstance('http', ['headers' => $headers]);
      $data = $fetcher->getResponseContent($url);
      // Convert objects to associative arrays.
      $source_data = json_decode($data, TRUE);
      $ids = array_map([__CLASS__, 'extractId'], $source_data['features']);
      $facilities = array_flip($ids);
    }

    return $facilities;
  }

  /**
   * Extracts the facility API id from the row.
   *
   * @param array $facility
   *   Array of data for a single row.
   *
   * @return string
   *   The facility api id.
   */
  protected static function extractId(array $facility) {
    return $facility['properties']['id'];
  }

  /**
   * Get all facility locator api ids in the CMS.
   *
   * @return array
   *   Array of facility data key value pairs of <fapi_id> => <nid>.
   */
  protected function getFacilitiesInCms(): array {
    // Will end up keyed <fapi_id> => nid.
    $facilities = [];
    $query = $this->database->select('node__field_facility_locator_api_id', 'fapi');
    // For now we are excluding CAPs, they are not in the facility API.
    $query->condition('field_facility_locator_api_id_value', '%CAP%', 'NOT LIKE');
    $query->condition('field_facility_locator_api_id_value', '%tricare%', 'NOT LIKE');
    $query->fields('fapi', ['field_facility_locator_api_id_value', 'entity_id']);
    $facility_fields = $query->execute()->fetchAll();
    if ($facility_fields) {
      foreach ($facility_fields as $facility) {
        $facilities[$facility->field_facility_locator_api_id_value] = $facility->entity_id;
      }
    }

    return $facilities;
  }

  /**
   * Get all archived facilities.
   *
   * @return array
   *   Array of facility node ids.
   */
  protected function getArchivedFacilities(): array {
    $facility_bundles = [
      'health_care_local_facility',
      'nca_facility',
      'vba_facility',
      'vet_center_mobile_vet_center',
      'vet_center_outstation',
      'vet_center',
    ];
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery();
    $query->condition('type', $facility_bundles, 'IN');
    $query->condition('moderation_state', 'archived');
    $query->accessCheck(FALSE);
    $archived_facilities = array_values($query->execute());
    return (array) $archived_facilities;
  }

  /**
   * Get all facilities in the CMS already flagged removed_from_source.
   *
   * @return array
   *   Array of facility node ids.
   */
  protected function getFlaggedRemovedFromSource(): array {
    $query = $this->database->select('flagging', 'flagging');
    $query->condition('entity_type', 'node', '=');
    $query->condition('flag_id', 'removed_from_source', '=');
    $query->fields('flagging', ['entity_id']);
    $facilities_flagged = $query->execute()->fetchCol();
    return (array) $facilities_flagged;
  }

  /**
   * Performs a sanity count on the facility api data.
   *
   * @param int $count_fapi
   *   A count of the facility api data returned.
   *
   * @return bool
   *   TRUE if suspicious, FALSE otherwise.
   */
  protected function facilityCountSeemsFaulty($count_fapi): bool {
    if ($count_fapi < 2000 || $count_fapi > 5000) {
      // Something is suspicious.
      return TRUE;
    }
    return FALSE;
  }

}
