<?php

namespace Drupal\va_gov_bulk;

use Drupal\Core\Entity\RevisionLogInterface;

/**
 * A Helper Class to assist with the publishing and unpublishing bulk action.
 *
 * Called by Publish Latest Revision and Unpublish Current Revision Bulk Ops.
 */
class AdminModeration {

  /**
   * Test Mode.
   *
   * @var bool
   */
  private $testMode = FALSE;

  /**
   * Entity.
   *
   * @var mixed
   */
  private $entity = NULL;

  /**
   * Nid.
   *
   * @var int
   */
  private $nid = 0;

  /**
   * Current Revision Id.
   *
   * @var int
   */
  private $currentRevisionId = 0;

  /**
   * Latest Revision Id.
   *
   * @var int
   */
  private $latestRevisionId = 0;

  /**
   * Status.
   *
   * @var int
   */
  private $status = 0;

  /**
   * {@inheritdoc}
   */
  public function __construct($entity, $status) {
    $this->entity = $entity;
    if (!is_null($status)) {
      $this->status = $status;
    }
    $this->nid = $this->entity->id();
  }

  /**
   * Unpublish current revision.
   */
  public function unpublish() {
    \Drupal::logger('AdminModeration-log')->notice(utf8_encode('Unpublish action in va_gov_bulk'));
    \Drupal::Messenger()->addStatus(utf8_encode('Unpublish action in va_gov_bulk'));
    $entity_manager = \Drupal::entityTypeManager();
    $this->entity->set('moderation_state', 'archived');

    if ($this->entity instanceof RevisionLogInterface) {
      $this->entity->setRevisionCreationTime(\Drupal::time()->getRequestTime());
      $msg = 'Bulk operation create archived revision';
      $this->entity->setRevisionLogMessage($msg);
      $current_uid = \Drupal::currentUser()->id();
      $this->entity->setRevisionUserId($current_uid);
    }
    $this->entity->save();
    $this->entity = $entity_manager->getStorage($this->entity->getEntityTypeId())->load($this->nid);
    $this->entity->set('moderation_state', 'draft');

    if ($this->entity instanceof RevisionLogInterface) {
      $this->entity->setRevisionCreationTime(\Drupal::time()->getRequestTime());
      $msg = 'Bulk operation create draft revision';
      $this->entity->setRevisionLogMessage($msg);
      $current_uid = \Drupal::currentUser()->id();
      $this->entity->setRevisionUserId($current_uid);
    }
    $this->entity->save();
    $this->entity = $entity_manager->getStorage($this->entity->getEntityTypeId())->load($this->nid);
    return $this->entity;
  }

  /**
   * Publish Latest Revision.
   */
  public function publish() {

    \Drupal::logger('AdminModeration-log')->notice(utf8_encode('Publish latest revision bulk operation'));
    \Drupal::Messenger()->addStatus(utf8_encode('Publish latest revision bulk operation'));
    $entity_manager = \Drupal::entityTypeManager();
    $this->entity->set('moderation_state', 'published');

    if ($this->entity instanceof RevisionLogInterface) {
      $this->entity->setRevisionCreationTime(\Drupal::time()->getRequestTime());
      $msg = 'Bulk operation publish revision';
      $this->entity->setRevisionLogMessage($msg);
      $current_uid = \Drupal::currentUser()->id();
      $this->entity->setRevisionUserId($current_uid);
    }
    $this->entity->save();
    $entity_manager = \Drupal::entityTypeManager();
    $this->entity = $entity_manager->getStorage('node')->load($this->nid);
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  private function privateSomething() {

    $this->entity->getTranslation("en")->set('moderation_state', 'published');
    $this->entity->getTranslation("fr")->set('moderation_state', 'published');

    $this->entity->save();
  }

}
