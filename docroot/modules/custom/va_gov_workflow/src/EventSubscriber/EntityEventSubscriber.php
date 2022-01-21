<?php

namespace Drupal\va_gov_workflow\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Form\FormBaseAlterEvent;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\node\NodeForm;
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
      HookEventDispatcherInterface::ENTITY_DELETE => 'entityDelete',
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
   */
  public function __construct(
    UserPermsService $user_perms_service,
    WorkflowContentControl $workflow_content_control,
    Flagger $flagger
  ) {
    $this->userPermsService = $user_perms_service;
    $this->workflowContentControl = $workflow_content_control;
    $this->flagger = $flagger;
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
   * Entity Delete Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent $event
   *   The event.
   */
  public function entityDelete(EntityDeleteEvent $event): void {
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'flagging') {
      // A flag is being deleted.
      $this->flagger->logFlagDeletion($entity);
    }
  }

}
