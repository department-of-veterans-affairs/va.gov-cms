<?php

namespace Drupal\va_gov_bulk\Service;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Encapsulates logic for bulk moderation actions.
 */
class ModerationActions implements ModerationActionsInterface {

  /**
   * Drupal\Core\Session\AccountProxyInterface definition.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Drupal\Component\Datetime\TimeInterface definition.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $datetimeTime;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ModerationActions object.
   */
  public function __construct(AccountProxyInterface $current_user, TimeInterface $datetime_time, EntityTypeManagerInterface $entity_type_manager) {
    $this->currentUser = $current_user;
    $this->datetimeTime = $datetime_time;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Archive the given node.
   *
   * @return \Drupal\node\NodeInterface
   *   The node.
   */
  public function archiveNode(NodeInterface $node) : NodeInterface {
    $node->set('moderation_state', 'archived');
    $node->setRevisionCreationTime($this->datetimeTime->getRequestTime());
    $node->setRevisionLogMessage('Bulk operation create archived revision');
    $node->setRevisionUserId($this->currentUser->id());
    $node->save();

    return $this->entityTypeManager->getStorage('node')->load($node->id());
  }

  /**
   * Publish the latest revision of the given node.
   *
   * @return \Drupal\node\NodeInterface
   *   The node.
   */
  public function publishLatestRevision(NodeInterface $node) : NodeInterface {
    $latest_revision_id = (string) $this->entityTypeManager
      ->getStorage('node')
      ->getLatestRevisionId($node->id());

    if ($node->vid->getString() !== $latest_revision_id) {
      $node = $this->entityTypeManager
        ->getStorage('node')
        ->loadRevision($latest_revision_id);
    }
    /** @var \Drupal\node\NodeInterface $node */
    $node->set('moderation_state', 'published');
    $node->setRevisionCreationTime($this->datetimeTime->getRequestTime());
    $node->setRevisionLogMessage('Bulk operation publish revision');
    $node->setRevisionUserId($this->currentUser->id());
    $node->save();

    return $this->entityTypeManager->getStorage('node')->load($node->id());
  }

}
