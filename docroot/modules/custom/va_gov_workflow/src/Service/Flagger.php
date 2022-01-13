<?php

namespace Drupal\va_gov_workflow\Service;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\flag\Entity\Flagging;
use Drupal\flag\FlagService;
use Drupal\node\NodeInterface;

/**
 * Service used for controlling flags on content.
 */
class Flagger {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The flag module flag service.
   *
   * @var \Drupal\flag\FlagService
   */
  protected $flagService;

  /**
   * Constructs the flagger object.
   *
   * @param \Drupal\flag\FlagService $flag_service
   *   The flag service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(FlagService $flag_service, EntityTypeManagerInterface $entity_type_manager) {
    $this->flagService = $flag_service;
    $this->entityTypeManager = $entity_type_manager;

  }

  /**
   * Sets a flag for the node.
   *
   * @param string $flag_id
   *   The machine name of the flag to set.
   * @param \Drupal\node\NodeInterface $node
   *   The node being operated on.
   * @param string $log_message
   *   The message to append to the revision log.
   * @param array $vars
   *   Message vars.
   */
  public function setFlag($flag_id, NodeInterface $node, $log_message = '', array $vars = []) {
    // Defend against presave of new node resulting in no nid.
    // A flag save can not happen if there is no nid, so don't operate
    // on a new node.
    if (!$node->isNew()) {
      $flag = $this->flagService->getFlagById($flag_id);
      if ($flag && !$this->isTestData()) {
        $this->flagService->flag($flag, $node);
        // NOTE:  This setting of the flag only works in non-form based node
        // saves like through migration.  On form-based node save, this flag
        // save will happen, but immediately gets undone when the flag form
        // saves because the flag is not set at that time (race condition).
        $flag_message_header = "Flagged: {$flag->get('flag_short')}";
        $log_message = "{$flag_message_header} - {$log_message}";
      }
    }

    $this->appendLog($node, $log_message, $vars);
  }

  /**
   * Appends a message to the revision log of the node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node that needs the revision log appended.
   * @param string $log_message
   *   The log message to append. (placeholders allowed)
   * @param array $vars
   *   Array of vars to put in message.
   */
  public function appendLog(NodeInterface $node, $log_message, array $vars = []) {
    if (!empty($log_message) && !$this->isTestData()) {
      $revision_log = $node->get('revision_log')->value;
      $log_entry = new FormattableMarkup($log_message, $vars);
      $revision_log .= (empty($revision_log)) ? $log_entry : PHP_EOL . $log_entry;
      $node->set('revision_log', $revision_log);
    }
  }

  /**
   * Determines if the node provided is test data.
   *
   * This is a hack to bypass flagging during phpunit tests.  The Flag interface
   * is not mocked so the contrib module throws fatal errors when test data
   * attempts to create a flag.  It is not ideal, but is a better alternative
   * than removing the phpunit save  tests.
   *
   * @return bool
   *   TRUE if test data detected, FALSE otherwise.
   */
  protected function isTestData() : bool {
    // Detect if phpunit is running.
    return (defined('PHPUNIT_COMPOSER_INSTALL')) ? TRUE : FALSE;
  }

  /**
   * Flag or message flagging of new. Must be called twice in order to work.
   *
   * @param string $flag_id
   *   The machine name of the flag to set.
   * @param \Drupal\node\NodeInterface $node
   *   The node being operated on.
   * @param string $log_message
   *   The message to append to the revision log.
   */
  public function flagNew($flag_id, NodeInterface $node, $log_message = '') {
    $first_save = (empty($node->original)) ? TRUE : FALSE;
    if ($node->isNew()) {
      // Can't actually set the flag, because no node id yet.
      // Just set the message. The flag will be set in entityCreate event.
      $flag = $this->flagService->getFlagById($flag_id);
      $flag_message_header = "Flagged: {$flag->get('flag_short')}";
      $log_message = "{$flag_message_header} - {$log_message}";
      $this->appendLog($node, $log_message);
    }
    elseif ($first_save) {
      // The message was set in presave, now we are in insert, so set flag.
      $this->setFlag($flag_id, $node);
    }
  }

  /**
   * Flag if a specific field changed.
   *
   * @param string $fieldname
   *   The machine name of field to check for changes.
   * @param string $flag_id
   *   The machine name of the flag to set.
   * @param \Drupal\node\NodeInterface $node
   *   The node being operated on.
   * @param string $log_message
   *   The message to append to the revision log.
   */
  public function flagFieldChanged($fieldname, $flag_id, NodeInterface $node, $log_message) {
    $original = $node->original ?? NULL;
    if (!$original) {
      // There is nothing to compare, so bail out.
      return;
    }
    $new_value = $node->get($fieldname)->value;
    $old_value = $original->get($fieldname)->value;
    if ($new_value !== $old_value) {
      // The field changed.  Set the flag.
      $vars = [
        '@old' => $old_value,
        '@new' => $new_value,
      ];
      $this->setFlag($flag_id, $node, $log_message, $vars);
    }
  }

  /**
   * Examines a flag on deletion and updated the node revision log.
   *
   * @param \Drupal\flag\Entity\Flagging $flagging
   *   The drupal node to examine.
   */
  public function logFlagDeletion(Flagging $flagging) {

    $type = $flagging->get('entity_type')->value;
    // Only supporting this for nodes.
    if ($type === 'node' && !$this->isTestData()) {
      /** @var \Drupal\flag\Entity\Flag $flag */
      $flag = $flagging->getFlag();
      $flagname = $flag->get('flag_short');
      $nid = $flagging->get('entity_id')->value;
      $vid = $this->entityTypeManager
        ->getStorage('node')
        ->getLatestRevisionId($nid);
      // The latest revision of the flagged node.
      /** @var \Drupal\node\NodeInterface $revision */
      $revision = $this->entityTypeManager
        ->getStorage('node')
        ->loadRevision($vid);

      $log_message = $revision->get('revision_log')->value;
      $flag_message = "Flag removed: {$flagname}" . PHP_EOL;
      $revision->set('revision_log', "{$flag_message} {$log_message}");
      // Avoid creating a new revision.  Just update the existing message.
      $revision->setNewRevision(FALSE);
      $revision->enforceIsNew(FALSE);
      $revision->setSyncing(TRUE);
      $revision->save();
    }
  }

}
