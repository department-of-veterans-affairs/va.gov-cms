<?php

namespace Drupal\va_gov_workflow_assignments\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Workflow Assignments Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  /**
   * The User Perms Service.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected $userPermsService;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The user perms service.
   */
  public function __construct(
    UserPermsService $user_perms_service
  ) {
    $this->userPermsService = $user_perms_service;
  }

  /**
   * Alter alert block form governance settings.
   *
   * Set alert block edit form status default to draft.
   *
   * Disable the scope field for non-admins.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterAlertBlocksEditForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form['#attached']['library'][] = 'va_gov_workflow_assignments/alert_block_treatment';
    $form['moderation_state']['widget'][0]['state']['#default_value'] = 'draft';
    if (!$this->userPermsService->hasAdminRole()) {
      $form['field_is_this_a_header_alert_']['widget']['#attributes']['disabled'] = TRUE;
      $form['field_node_reference']['#disabled'] = TRUE;
    }
  }

  /**
   * Disable the scope field for non-admins.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterAlertBlocksForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form['#attached']['library'][] = 'va_gov_workflow_assignments/alert_block_treatment';
    if (!$this->userPermsService->hasAdminRole()) {
      $form['field_node_reference']['#disabled'] = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // React on alert block forms.
      'hook_event_dispatcher.form_block_content_alert_edit_form.alter' => 'alterAlertBlocksEditForm',
      'hook_event_dispatcher.form_block_content_alert_form.alter' => 'alterAlertBlocksForm',

    ];
  }

}
