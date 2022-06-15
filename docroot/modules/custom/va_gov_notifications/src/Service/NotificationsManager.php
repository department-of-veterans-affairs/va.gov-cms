<?php

namespace Drupal\va_gov_notifications\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\message_notify\MessageNotifier;
use Drupal\message\Entity\Message;
use Drupal\node\NodeInterface;

/**
 * Service used for VA notifications.
 */
class NotificationsManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * The message notifier (sender).
   *
   * @var \Drupal\message_notify\MessageNotifier
   */
  public $messageNotifier;

  /**
   * Constructs the NotificationManager object.
   *
   * @param \Drupal\message_notify\MessageNotifier $message_notifier
   *   Message notifier (sender).
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(MessageNotifier $message_notifier, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->messageNotifier = $message_notifier;
  }

  /**
   * Sends a message using a template.
   *
   * @param string $template_name
   *   The machine name of the template to use.
   * @param int $user_id
   *   The user id to send the notification to.
   * @param array $values
   *   Array of key value pairs that will be passed to the template.
   *
   * @return bool
   *   Boolean value denoting success or failure of the notification.
   */
  public function send($template_name, $user_id, array $values = []) : bool {
    // We bypass sending email if testing because test running in parallel
    // cause a theme error that break tests unnecessarily.
    if (!$this->isTest()) {
      $message = Message::create([
        'template' => $template_name,
        'uid' => $user_id,
      ]);
      foreach ($values as $field_name => $value) {
        $message->set($field_name, $value);
      }

      $message->save();
      // Send message to message user specified user.
      return $this->messageNotifier->send($message);
    }
    return FALSE;
  }

  /**
   * Checks if a testing flag is set.
   *
   * Use this sparingly, and only if there is no other way.
   *
   * @return bool
   *   TRUE if testing flag is set, FALSE otherwise.
   */
  protected function isTest() {
    return (defined('IS_BEHAT')) ? IS_BEHAT : FALSE;
  }

  /**
   * Create a standardized message vars.
   *
   * @param \Drupal\node\NodeInterface $targetEntity
   *   A target entity to be referenced by the message.
   * @param string $msg_title_prefix
   *   A string to prefix the message with ('New form:').
   *
   * @return array
   *   Array of standardized vars to pass to all message templates.
   */
  public function buildMessageFields(NodeInterface $targetEntity, $msg_title_prefix = '') {
    $message_fields = [
      'field_target_node_title' => "{$msg_title_prefix} {$targetEntity->getTitle()}",
      'field_target_entity' => $targetEntity->id(),
    ];
    return $message_fields;
  }

  /**
   * Send a message based on if a field value has changed or not.
   *
   * Based on va_gov_workflow\Service\Flagger->flagFieldChanged().
   *
   * @param string|array $fieldname
   *   The machine name of field to check for changes. or an array of field name
   *   followed by field property.
   * @param \Drupal\node\NodeInterface $node
   *   A target entity to be referenced by the message.
   * @param string $msg_title_prefix
   *   A string to prefix the message with ('New form:').
   * @param string $template_name
   *   The machine name of the message template to use.
   * @param string $user_id
   *   The user id to send the message to.
   */
  public function sendMessageOnFieldChange($fieldname, NodeInterface $node, $msg_title_prefix, $template_name, $user_id) {
    $original = $this->getPreviousRevision($node);
    if (!$original || $node->isSyncing()) {
      // There is nothing to compare, so bail out.
      return;
    }
    $new_value = (is_array($fieldname)) ? $node->get($fieldname[0])->{$fieldname[1]} : $node->get($fieldname)->value;
    $old_value = (is_array($fieldname)) ? $original->get($fieldname[0])->{$fieldname[1]} : $original->get($fieldname)->value;
    // Loose comparison needed because sometimes new is 0 and old is '0'.
    if ($new_value != $old_value) {
      // The field changed.  Send the message.
      $message_fields = $this->buildMessageFields($node, $msg_title_prefix);
      $this->send($template_name, $user_id, $message_fields);
    }
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

}
