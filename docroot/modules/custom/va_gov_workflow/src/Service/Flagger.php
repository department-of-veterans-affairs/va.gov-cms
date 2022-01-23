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
        // NOTE:  This setting of the flag only works in non-form based node
        // saves like through migration.  On form-based node save, this flag
        // save will happen, but immediately gets undone when the flag form
        // saves because the flag is not set at that time (race condition).
        if (!$flag->isFlagged($node)) {
          // Has not already been flagged, proceed to flag it.
          $this->flagService->flag($flag, $node);
        }

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
   * @param string|array $fieldname
   *   The machine name of field to check for changes. or an array of field name
   *   followed by field property.
   * @param string $flag_id
   *   The machine name of the flag to set.
   * @param \Drupal\node\NodeInterface $node
   *   The node being operated on.
   * @param string $log_message
   *   The message to append to the revision log.
   */
  public function flagFieldChanged($fieldname, $flag_id, NodeInterface $node, $log_message) {
    $original = $this->getPreviousRevision($node);
    if (!$original) {
      // There is nothing to compare, so bail out.
      return;
    }
    $new_value = (is_array($fieldname)) ? $node->get($fieldname[0])->{$fieldname[1]} : $node->get($fieldname)->value;
    $old_value = (is_array($fieldname)) ? $original->get($fieldname[0])->{$fieldname[1]} : $original->get($fieldname)->value;
    // Loose comparison needed because sometimes new is 0 and old is '0'.
    if ($new_value != $old_value) {
      // The field changed.  Set the flag.
      $vars = [
        '@old' => $old_value,
        '@new' => $new_value,
      ];
      $this->setFlag($flag_id, $node, $log_message, $vars);
    }
  }

  /**
   * Logs a flag operation on the the node revision log.
   *
   * @param \Drupal\flag\Entity\Flagging $flagging
   *   The drupal node to examine.
   * @param string $operation
   *   The operation being performed on the flag (create, delete).
   */
  public function logFlagOperation(Flagging $flagging, $operation) {
    $type = $flagging->get('entity_type')->value;
    // Only supporting this for nodes.
    if ($type === 'node' && !$this->isTestData()) {
      /** @var \Drupal\flag\Entity\Flag $flag */
      $flag = $flagging->getFlag();
      $flagname = $flag->get('flag_short');

      if ($operation === 'create') {
        $flag_message = "Flagged: {$flagname}" . PHP_EOL;
      }
      elseif ($operation === 'delete') {
        $flag_message = "Flag removed: {$flagname}" . PHP_EOL;
      }

      if ($flag_message) {
        $nid = $flagging->get('entity_id')->value;
        $revision = $this->getLatestRevision($nid);
        $log_message = $revision->get('revision_log')->value;
        $revision->set('revision_log', "{$flag_message} {$log_message}");
        // Avoid creating a new revision.  Just update the existing message.
        $revision->setNewRevision(FALSE);
        $revision->enforceIsNew(FALSE);
        $revision->setSyncing(TRUE);
        $revision->save();
      }
    }
  }

  /**
   * Get the lastest revision of the node.
   *
   * @param int $nid
   *   The node id for the revision to get.
   *
   * @return \Drupal\node\NodeInterface
   *   The latest revision.
   */
  protected function getLatestRevision(int $nid) {
    $vid = $this->entityTypeManager
      ->getStorage('node')
      ->getLatestRevisionId($nid);
    // The latest revision of the flagged node.
    /** @var \Drupal\node\NodeInterface $revision */
    $revision = $this->entityTypeManager
      ->getStorage('node')
      ->loadRevision($vid);

    return $revision;
  }

  /**
   * Gets the previous revision.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The current node being operated on.
   *
   * @return \Drupal\node\NodeInterface
   *   Either the true Original (default Revision) or the latest revision.
   */
  protected function getPreviousRevision(NodeInterface $node) {
    $original = $node->original ?? NULL;
    // PROBLEM: original is the default revision, so it may look like a
    // continual change if previous save was draft on a published revision.
    $was_published = ($original instanceof NodeInterface) ? $original->isPublished() : FALSE;
    if ($was_published) {
      // Then original might not be the most recent draft revision.
      // Get the latest revision to be sure.
      $nid = $node->id();
      $original = $this->getLatestRevision($nid);
    }

    return $original;
  }

}
