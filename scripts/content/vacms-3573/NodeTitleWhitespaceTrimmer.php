<?php

use Drupal\node\NodeStorageInterface;
use Drush\Log\LogLevel;
use Drupal\Core\Language\LanguageInterface;
use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\content_lock\ContentLock\ContentLock;
use Psr\Log\LoggerInterface;

/**
 * Provides a mechanism to fix node titles with leading/trailing whitespace.
 */
class NodeTitleWhitespaceTrimmer {
  /**
   * This user id is used for locking and node saving.
   */
  const CMS_MIGRATOR_ID = 1317;

  /**
   * Node types that are excluded from this process.
   */
  const NODE_TYPE_EXCEPTIONS = [
    'health_care_local_facility',
    'nca_facility',
    'vba_facility',
    'vet_center_cap',
    'vet_center_outstation',
    'vet_center',
    'banner',
    'full_width_banner_alert',
    'va_form',
  ];

  /**
   * Node storage service.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  private $nodeStorage;

  /**
   * Moderation information service.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  private $moderationInformation;

  /**
   * Content lock service.
   *
   * @var \Drupal\content_lock\ContentLock\ContentLock
   */
  private $lockService;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * Construct the trimmer for use.
   *
   * @param \Drupal\node\NodeStorageInterface $nodeStorage
   *   The NodeStorage service.
   * @param \Drupal\content_moderation\ModerationInformationInterface $moderationInformation
   *   The ModerationInformation service.
   * @param \Drupal\content_lock\ContentLock\ContentLock $lockService
   *   The ContentLock service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(NodeStorageInterface $nodeStorage, ModerationInformationInterface $moderationInformation, ContentLock $lockService, LoggerInterface $logger) {
    $this->nodeStorage = $nodeStorage;
    $this->moderationInformation = $moderationInformation;
    $this->lockService = $lockService;
    $this->logger = $logger;
  }

  /**
   * Runs the node update process.
   */
  public function run() {
    $nids_to_process = $this->retrieveNodesToUpdate();
    $nids_to_process_count = count($nids_to_process);
    $query_limit = 25;
    $nids_modified_count = 0;

    $this->logMessage('Found %total_nodes nodes to process.', ['%total_nodes' => $nids_to_process_count]);

    while (count($nids_to_process)) {
      $this->logMessage('%remaining_count nodes left to process.', ['%remaining_count' => count($nids_to_process)]);
      // Load entities.
      $modified_nids = [];
      $node_ids = array_slice($nids_to_process, 0, $query_limit, TRUE);
      $nodes = $this->nodeStorage->loadMultiple($node_ids);
      /** @var \Drupal\node\NodeInterface[] $nodes */
      foreach ($nodes as $node) {
        // Save some time by only saving nodes that need adjustments.
        // Skip nodes with pending revisions. Lock the node or else pass over.
        if (
          (
            preg_match('/\s+$/', $node->getTitle())
            ||
            preg_match('/^\s+/', $node->getTitle())
          )
          &&
          !$this->moderationInformation->hasPendingRevision($node)
          &&
          $this->lockNode($node->id())
        ) {
          // Make this change a new revision.
          $node->setNewRevision(TRUE);

          // Set revision author to uid 1317 (CMS Migrator user).
          $node->setRevisionUserId(static::CMS_MIGRATOR_ID);
          $node->setChangedTime(time());
          $node->setRevisionCreationTime(time());
          $title_field_label = $node->getFieldDefinition('title')->getLabel();
          $new_title = preg_replace('/^\s+/', '', $node->getTitle());
          $new_title = preg_replace('/\s+$/', '', $new_title);
          $substitutions = [
            '%title_field_label' => $title_field_label,
            '%original_title' => $node->getTitle(),
            '%new_title' => $new_title,
          ];
          $revision_message_template = 'The %title_field_label field was updated from "%original_title" to "%new_title" to remove extra spaces. No other changes were made and this change does not affect how the title appears to veterans.';
          $revision_message = strtr($revision_message_template, $substitutions);
          // Set revision log message.
          $node->setRevisionLogMessage($revision_message);
          $node->save();
          $this->unlockNode($node->id());
          $modified_nids[] = $node->id();
          $nids_modified_count += 1;
        }
        unset($nids_to_process["node_{$node->id()}"]);
      }

      // Log the processed nodes.
      if (count($modified_nids)) {
        $this->logMessage('Nodes updated this pass: %nids', [
          '%nids' => implode(', ', $modified_nids),
        ]);
      }

    }
    $this->logMessage('Checked %count nodes and corrected title issues on %total nodes.', [
      '%count' => $nids_to_process_count,
      '%total' => $nids_modified_count,
    ]);

  }

  /**
   * Returns the set of nodes to examine and correct.
   *
   * @return array
   *   An array of nids to update.
   */
  private function retrieveNodesToUpdate(): array {
    $query = $this->nodeStorage->getQuery();
    $nids_to_update = $query
      ->condition('type', static::NODE_TYPE_EXCEPTIONS, 'NOT IN')
      ->execute();
    return array_combine(
      array_map([$this, 'stringifynid'], array_values($nids_to_update)),
      array_values($nids_to_update));
  }

  /**
   * Combination logging and printing of log messages.
   *
   * @param string $message
   *   The message to print and log.
   * @param array $context
   *   Any contextual information to pass to the message, i.e. values.
   * @param string $logLevel
   *   The LogLevel constant for desired log level.
   */
  private function logMessage($message, array $context = [], $logLevel = LogLevel::INFO) {
    $context = array_merge(['uid' => static::CMS_MIGRATOR_ID], $context);
    $this->logger->log($logLevel, $message, $context);
    print strtr($message, $context) . PHP_EOL;
  }

  /**
   * Lock the specified node.
   *
   * @param int $nid
   *   The node ID.
   */
  private function lockNode(int $nid): bool {
    if (!$this->lockService->locking($nid, LanguageInterface::LANGCODE_NOT_SPECIFIED, '*', static::CMS_MIGRATOR_ID)) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Unlock a specified node.
   *
   * @param int $nid
   *   The node ID.
   */
  private function unlockNode(int $nid) {
    $this->lockService->release($nid, LanguageInterface::LANGCODE_NOT_SPECIFIED, '*', static::CMS_MIGRATOR_ID);
    if ($this->nodeIsLocked($nid)) {
      throw new \Exception("Could not unlock node {$nid}.");
    }
  }

  /**
   * Check whether the specified node is locked.
   *
   * @param int $nid
   *   The node ID.
   *
   * @return bool
   *   TRUE if the node is locked, otherwise FALSE.
   */
  private function nodeIsLocked(int $nid): bool {
    return $this->lockService->isLockedBy($nid, LanguageInterface::LANGCODE_NOT_SPECIFIED, '*', static::CMS_MIGRATOR_ID);
  }

  /**
   * Callback function to concat node ids with string.
   *
   * @param int $nid
   *   The node id.
   *
   * @return string
   *   The node id concatenated to the end o node_
   */
  private function stringifynid($nid): string {
    return "node_$nid";
  }

}

// Collect the services to use with the trimmer.
$nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
$moderationInformation = \Drupal::service('content_moderation.moderation_information');
$lockService = $lock_service = \Drupal::service('content_lock');
$logger = Drupal::logger('scripts/content/VACMS-3573');

$trimmer = new NodeTitleWhitespaceTrimmer($nodeStorage, $moderationInformation, $lockService, $logger);
$trimmer->run();
