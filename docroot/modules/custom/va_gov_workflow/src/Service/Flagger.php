<?php

namespace Drupal\va_gov_workflow\Service;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\flag\Entity\Flagging;
use Drupal\flag\FlagService;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;

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
    // Don't set flags on a syncing operation, like updating a revision log
    // after the fact, or we end up with recursion.
    if (!$node->isSyncing()) {
      $flag = $this->flagService->getFlagById($flag_id);
      if ($flag && !$this->isTestData()) {
        // NOTE:  This setting of the flag only works in non-form based node
        // saves like through migration.  On form-based node save, this flag
        // save will happen, but immediately gets undone when the flag form
        // saves because the flag is not set at that time (race condition).
        if (!$flag->isFlagged($node)) {
          // Has not already been flagged, proceed to flag it.
          $this->flagService->flag($flag, $node, $this->getAccount());
          $this->updateRevisonLog($node->id(), $log_message, $vars);
        }

      }
    }
  }

  /**
   * Updates a message to the revision log of the latest node revision.
   *
   * @param int $nid
   *   The node that needs the revision log appended.
   * @param string $message
   *   The log message to append. (placeholders allowed)
   * @param array $msg_vars
   *   Array of vars to put in message.
   * @param bool $prepend
   *   TRUE prepends the message to the revision log, FALSE appends msg.
   */
  public function updateRevisonLog(int $nid, $message, array $msg_vars = [], $prepend = FALSE) {
    if (!empty($message) && !$this->isTestData()) {
      $revision = $this->getLatestRevision($nid);
      if ($revision) {
        $existing_message = $revision->getRevisionLogMessage();
        $log_update = new FormattableMarkup($message, $msg_vars);
        $log_update = $log_update->__toString();
        // Prevent recursive messaging.
        if (strpos($existing_message, $log_update) === FALSE) {
          // The message does not exist previously, so add it.
          $newline = (empty($existing_message)) ? '' : PHP_EOL;
          $existing_message = ($prepend) ? "{$log_update}{$newline} {$existing_message}" : "{$existing_message} {$newline}{$log_update}";
          $revision->setRevisionLogMessage($existing_message);
          // Avoid creating a new revision.  Just update the existing message.
          $revision->setNewRevision(FALSE);
          $revision->enforceIsNew(FALSE);
          $revision->setSyncing(TRUE);
          $revision->save();
        }
      }
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
   *
   * @return bool
   *   TRUE if performed, FALSE otherwise.
   */
  public function flagNew($flag_id, NodeInterface $node, $log_message = '') {
    $first_save = (empty($node->original)) ? TRUE : FALSE;
    if ($node->isNew() || $first_save) {
      // Can't actually set the flag, because no node id yet.
      // Just set the message. The flag will be set in entityCreate event.
      $this->setFlag($flag_id, $node);
      $this->updateRevisonLog($node->id(), $log_message);
      return TRUE;
    }
    return FALSE;
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
   *
   * @return bool
   *   TRUE if performed, FALSE otherwise.
   */
  public function flagFieldChanged($fieldname, $flag_id, NodeInterface $node, $log_message) {
    $original = $this->getPreviousRevision($node);
    if (!$original || $node->isSyncing()) {
      // There is nothing to compare, so bail out.
      return FALSE;
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
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Logs a flag operation on the node revision log.
   *
   * @param \Drupal\flag\Entity\Flagging $flagging
   *   The drupal node to examine.
   * @param string $operation
   *   The operation being performed on the flag (create, delete).
   */
  public function logFlagOperation(Flagging $flagging, $operation) {
    $type = $flagging->get('entity_type')->value;
    // Only supporting this for nodes.
    if ($type === 'node' && $this->isLoggableFlag($flagging)) {
      /** @var \Drupal\flag\Entity\Flag $flag */
      $flag = $flagging->getFlag();
      $flagname = $flag->get('flag_short');

      if ($operation === 'create') {
        $flag_message = "Flagged: {$flagname}";
      }
      elseif ($operation === 'delete') {
        $flag_message = "Flag removed: {$flagname}";
      }

      if (!empty($flag_message)) {
        $nid = $flagging->get('entity_id')->value;
        $this->updateRevisonLog($nid, $flag_message, [], TRUE);
      }
    }
  }

  /**
   * Checks to see if this flag is loggable.
   *
   * @param \Drupal\flag\Entity\Flagging $flagging
   *   The drupal node to examine.
   *
   * @return bool
   *   TRUE if loggable. FALSE otherwise.
   */
  protected function isLoggableFlag(Flagging $flagging) : bool {
    /** @var \Drupal\flag\Entity\Flag $flag */
    $flag = $flagging->getFlag();
    if ($this->isTestData() || !$flag->isGlobal()) {
      // Don't log test data changes or personal flags.
      return FALSE;
    }
    $non_loggable_flags = ['edited'];

    $flagname = $flag->get('flag_short');

    return (in_array($flagname, $non_loggable_flags)) ? FALSE : TRUE;
  }

  /**
   * Get the latest revision of the node.
   *
   * @param int $nid
   *   The node id for the revision to get.
   * @param \Drupal\node\NodeInterface|null $node
   *   Optional, if $node is provided it is an indication that we have the
   *   current revision, so we have to go get the previous revision.
   *
   * @return \Drupal\node\NodeInterface
   *   The latest revision.
   */
  protected function getLatestRevision(int $nid, $node = NULL) {
    $storage = $this->entityTypeManager
      ->getStorage('node');
    if ($node) {
      $vids = $storage->revisionIds($node);
      $previous_vid = $vids[count($vids) - 2];
    }
    else {
      $previous_vid = $storage->getLatestRevisionId($nid);
    }

    // The latest revision of the flagged node.
    /** @var \Drupal\node\NodeInterface $revision */
    $revision = $this->entityTypeManager
      ->getStorage('node')
      ->loadRevision($previous_vid);

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
      $original = $this->getLatestRevision($nid, $node);
    }

    return $original;
  }

  /**
   * Gets a default account for Drush based changes.
   *
   * @return mixed
   *   NULL normally, but the CMS migrator account if run by CLI.
   */
  protected function getAccount() {
    // In the case of CLI (a drush command), we need to provide
    // the CMS migrator user.
    return (PHP_SAPI === 'cli') ? User::load(1317) : NULL;
  }

}
