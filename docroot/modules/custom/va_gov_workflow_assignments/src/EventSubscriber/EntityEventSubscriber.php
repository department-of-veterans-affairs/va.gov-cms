<?php

namespace Drupal\va_gov_workflow_assignments\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Workflow Assignments Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {
  /**
   * The active user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   *  The user object.
   */
  private $currentUser;

  /**
   * The User Perms Service.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected $userPermsService;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The user perms service.
   */
  public function __construct(
    AccountInterface $currentUser,
    UserPermsService $user_perms_service
  ) {
    $this->currentUser = $currentUser;
    $this->userPermsService = $user_perms_service;
  }

  /**
   * Alter alert block form governance settings.
   *
   * Set alert block edit form status default to draft.
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
      $form['field_node_reference']['#access'] = FALSE;
    }
  }

  /**
   * Alter alert block form governance settings.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterAlertBlocksForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form['#attached']['library'][] = 'va_gov_workflow_assignments/alert_block_treatment';
    if (!$this->userPermsService->hasAdminRole()) {
      $form['field_node_reference']['#access'] = FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // React on alert block edit forms.
      'hook_event_dispatcher.form_block_content_alert_edit_form.alter' => 'alterAlertBlocksEditForm',
      // React on alert block add forms.
      'hook_event_dispatcher.form_block_content_alert_form.alter' => 'alterAlertBlocksForm',
    ];
  }

}
