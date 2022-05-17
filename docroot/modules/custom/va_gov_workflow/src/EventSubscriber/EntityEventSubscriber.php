<?php

namespace Drupal\va_gov_workflow\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\core_event_dispatcher\Event\Form\FormBaseAlterEvent;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeForm;
use Drupal\va_gov_notifications\Service\NotificationsManager;
use Drupal\va_gov_user\Service\UserPermsService;
use Drupal\va_gov_workflow\Service\Flagger;
use Drupal\va_gov_workflow\Service\WorkflowContentControl;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Workflow Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {


  /**
   * The vagov workflow flagger service.
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
   * The User Perms Service.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected $userPermsService;

  /**
   * The Workflow content control Service.
   *
   * @var \Drupal\va_gov_workflow\Service\WorkflowContentControl
   */
  protected $workflowContentControl;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      'hook_event_dispatcher.form_base_node_form.alter' => 'alterNodeForm',
      EntityHookEvents::ENTITY_DELETE => 'entityDelete',
      EntityHookEvents::ENTITY_INSERT => 'entityInsert',
      EntityHookEvents::ENTITY_UPDATE => 'entityUpdate',
    ];
  }

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The user perms service.
   * @param \Drupal\va_gov_workflow\Service\WorkflowContentControl $workflow_content_control
   *   The workflow content control service.
   * @param \Drupal\va_gov_workflow\Service\Flagger $flagger
   *   The vagov workflow flagger service.
   * @param \Drupal\va_gov_notifications\Service\NotificationsManager $notifications_manager
   *   VA gov NotificationsManager service.
   */
  public function __construct(
    UserPermsService $user_perms_service,
    WorkflowContentControl $workflow_content_control,
    Flagger $flagger,
    NotificationsManager $notifications_manager
  ) {
    $this->userPermsService = $user_perms_service;
    $this->workflowContentControl = $workflow_content_control;
    $this->flagger = $flagger;
    $this->notificationsManager = $notifications_manager;
  }

  /**
   * Entity update Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event.
   */
  public function entityUpdate(EntityUpdateEvent $event): void {
    $entity = $event->getEntity();
    $this->flagVaFormChanges($entity);
  }

  /**
   * Alters to be applied to all content types.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormBaseAlterEvent $event
   *   The event.
   */
  public function alterNodeForm(FormBaseAlterEvent $event) {
    $form_state = $event->getFormState();
    if ($form_state->getFormObject() instanceof EntityFormInterface) {
      $this->removeArchiveOption($event);
    }
  }

  /**
   * Removes archive option for non-admins on certain content types.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormBaseAlterEvent $event
   *   The event.
   */
  private function removeArchiveOption(FormBaseAlterEvent $event) {
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    if ($form_state->getFormObject() instanceof NodeForm) {
      /** @var \Drupal\node\NodeInterface $node **/
      $node = $form_state->getFormObject()->getEntity();
      $bundle = $node->bundle();
      $is_admin = $this->userPermsService->hasAdminRole();
      $is_archiveable = $this->workflowContentControl->isBundleArchiveableByNonAdmins($bundle);
      if (!$is_admin && !$is_archiveable) {
        unset($form['moderation_state']['widget'][0]['state']['#options']['archived']);
      }
    }
  }

  /**
   * Flag VA Forms if certain changes are made.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity of unknown type.
   */
  protected function flagVaFormChanges(EntityInterface $entity) {
    if ($entity->bundle() === 'va_form') {
      $this->flagger->flagFieldChanged('field_va_form_title', 'changed_title', $entity, "The form title of this form changed from '@old' to '@new' in the Forms DB.");
      $this->flagger->flagFieldChanged(['field_va_form_url', 'uri'], 'changed_filename', $entity, "The file name (URL) of this form changed from '@old' to '@new' in the Forms DB.");
      $this->flagger->flagFieldChanged('field_va_form_deleted', 'deleted', $entity, "The form was marked as deleted in the Forms DB.");
    }
  }

  /**
   * Entity Insert Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event.
   */
  public function entityInsert(EntityInsertEvent $event): void {
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'flagging') {
      // A flag is being added.
      $this->flagger->logFlagOperation($entity, 'create');
    }
    elseif ($entity->bundle() === 'va_form') {
      $this->flagger->flagNew('new_form', $entity, "This VA Form was added to the Forms DB.");
      // @codingStandardsIgnoreStart
      // Sample code for building a message notification. Using swirt's user id
      // for now.
      // $message_fields = $this->notificationsManager->buildMessageFields($entity, 'New form:');
      // $this->notificationsManager->send('va_form_new_form', 1215, $message_fields);
      // @codingStandardsIgnoreEnd
    }
  }

  /**
   * Entity Delete Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent $event
   *   The event.
   */
  public function entityDelete(EntityDeleteEvent $event): void {
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'flagging') {
      // A flag is being deleted.
      $this->flagger->logFlagOperation($entity, 'delete');
    }
  }

}
