<?php

namespace Drupal\va_gov_workflow\Service;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\flag\FlagService;
use Drupal\node\NodeInterface;

/**
 * Service used for controlling flags on content.
 */
class Flagger {
  /**
   * The flag module flag service.
   *
   * @var \Drupal\flag\FlagService
   */
  protected $flagService;

  /**
   * Constructs the flagger object.
   *
   * @param Drupal\flag\FlagService $flag_service
   *   The flag service.
   */
  public function __construct(FlagService $flag_service) {
    $this->flagService = $flag_service;
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
    // Defend against presave of new resulting in no nid.
    if (!$node->isNew()) {
      // A flag save can not happen if there is no nid, so can't operate
      // on a new node.
      $flag = $this->flagService->getFlagById($flag_id);
      $this->flagService->flag($flag, $node);
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
    if (!empty($log_message)) {
      $revision_log = $node->get('revision_log')->value;
      $log_entry = new FormattableMarkup($log_message, $vars);
      $revision_log .= (empty($revision_log)) ? $log_entry : PHP_EOL . $log_entry;
      $node->set('revision_log', $revision_log);
    }
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
      // Just set the message.
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
   * Examines all the flags for node to determine if any changed.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The drupal node to examine.
   */
  public function logFlagChanges(NodeInterface $node) {
    // Figure out how to get all flags from original??
    // ->getAllFlags('node', 'article');
    // then ->getEntityFlaggings(FlagInterface $flag, EntityInterface $entity)
    // or -> getAllEntityFlaggings(EntityInterface $entity)
  }

}
