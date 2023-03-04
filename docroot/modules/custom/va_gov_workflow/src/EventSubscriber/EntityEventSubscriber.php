<?php

namespace Drupal\va_gov_workflow\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\core_event_dispatcher\Event\Form\FormBaseAlterEvent;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeForm;
use Drupal\node\NodeInterface;
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
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   *  The entityType manager.
   */
  private $entityTypeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entityTypeManager.
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
    EntityTypeManagerInterface $entity_type_manager,
    UserPermsService $user_perms_service,
    WorkflowContentControl $workflow_content_control,
    Flagger $flagger,
    NotificationsManager $notifications_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
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
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    $form_id = $event->getFormId();
    if ($form_state->getFormObject() instanceof EntityFormInterface) {
      $this->removeArchiveOption($event);
    }
    $this->requireRevisionMessage($form, $form_state, $form_id);
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
      $message_fields = $this->notificationsManager->buildMessageFields($entity);
      if ($this->flagger->flagFieldChanged('field_va_form_title', 'changed_title', $entity, "The form title of this form changed from '@old' to '@new' in the Forms DB.")) {
        $this->notificationsManager->send('va_form_changed_title', '#va-forms', $message_fields, 'slack');
      }

      if ($this->flagger->flagFieldChanged(['field_va_form_url', 'uri'], 'changed_filename', $entity, "The file name (URL) of this form changed from '@old' to '@new' in the Forms DB.")) {
        $this->notificationsManager->send('va_form_changed_url', '#va-forms', $message_fields, 'slack');
      }

      if ($this->flagger->flagFieldChanged('field_va_form_deleted', 'deleted', $entity, "The form was marked as deleted in the Forms DB.")) {
        $this->notificationsManager->send('va_form_deleted', '#va-forms', $message_fields, 'slack');
      }
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
      $flagged = $this->flagger->flagNew('new_form', $entity, "This VA Form was added to the Forms DB.");
      if ($flagged) {
        $message_fields = $this->notificationsManager->buildMessageFields($entity);
        $this->notificationsManager->send('va_form_new_form', '#va-forms', $message_fields, 'slack');
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
      $this->flagger->logFlagOperation($entity, 'delete');
    }
  }

  /**
   * Adds Validation to check revision log message is added.
   *
   * @param array $form
   *   The exposed widget form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param string $form_id
   *   The form id.
   */
  public function requireRevisionMessage(array &$form, FormStateInterface &$form_state, $form_id) {
    $form['revision_log']['#required'] = TRUE;
    $form['revision_log']['widget']['#required'] = TRUE;
    $form['revision_log']['widget'][0]['#required'] = TRUE;
    $form['revision_log']['widget'][0]['value']['#required'] = TRUE;
    $form['#validate'][] = '_va_gov_workflow_validate_required_revision_message';
    // Hide the checkbox that lets you opt into making a revision.
    $form["revision_information"]["#attributes"]['class'][] = 'visually-hidden';
    $this->bypassRevisionLogValidationOnIef($form, $form_state);
  }

  /**
   * Bypasses required revision log for validation for Inline Entity Forms.
   *
   * @param array $form
   *   The form array by reference.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  protected function bypassRevisionLogValidationOnIef(array &$form, FormStateInterface $form_state): void {
    /** @var \Drupal\node\NodeInterface $node **/
    $node = $form_state->getFormObject()->getEntity();
    $ief_fields = $this->getIefTypeFields($node);
    $operation_button_map = [
      'field_widget_edit' => 'edit_button',
      'field_widget_remove' => 'remove_button',
      'field_widget_replace' => 'replace_button',
    ];

    foreach ($ief_fields as $ief_field_name => $widget_operations) {
      $widget_operations = array_intersect_key($operation_button_map, $widget_operations);
      // Stop the node form validation from firing on ief operation buttons.
      $current_widgets = $form[$ief_field_name]['widget']['current'] ?? [];
      foreach ($current_widgets as $key => $widget_details) {
        // The array contains a mix of numeric keys and named keys. The numeric
        // are the ones with button actions that we must intercept.
        if (is_numeric($key)) {
          foreach ($widget_operations as $widget_button) {
            if ($widget_button && isset($form[$ief_field_name]['widget']['current'][$key]['actions'][$widget_button])) {
              $form[$ief_field_name]['widget']['current'][$key]['actions'][$widget_button]['#limit_validation_errors'] = [[$ief_field_name]];
            }
          }
        }
      }
    }

  }

  /**
   * Get all fields on node that are IEF fields that result node edit/save.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to pull the fields from.
   *
   * @return array
   *   An array whose elements look like 'field_name' => [widget operations].
   */
  protected function getIefTypeFields(NodeInterface $node): array {
    $ief_fields = [];
    // Load all of the form displays for the given bundle.
    /** @var \Drupal\Core\Entity\Entity\EntityFormDisplay $form_displays */
    $form_displays = $this->entityTypeManager->getStorage('entity_form_display')->loadByProperties([
      'targetEntityType' => 'node',
      'bundle' => $node->bundle(),
    ]);
    $form_display = reset($form_displays);
    $field_displays = $form_display->toArray();

    foreach ($field_displays['content'] as $field_name => $field_display) {
      if ($this->isNodeIef($node, $field_name)) {
        $operations = [
          'field_widget_edit' => !empty($field_display['settings']['field_widget_edit']),
          'field_widget_remove' => !empty($field_display['settings']['field_widget_remove']),
          'field_widget_replace' => !empty($field_display['settings']['field_widget_replace']),
        ];
        $operations = array_filter($operations);
        $ief_fields[$field_name] = $operations;
      }
    }

    return $ief_fields;
  }

  /**
   * Checks to see if a field is an entity reference that targets a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   * @param string $field_name
   *   The machine name of the field being checked.
   *
   * @return bool
   *   TRUE if it is an ief field targeting a node, FALSE otherwise.
   */
  protected function isNodeIef(NodeInterface $node, $field_name): bool {
    $field_definition = $node->getFieldDefinition($field_name);
    if (empty($field_definition)) {
      return FALSE;
    }
    $fieldType = $field_definition->getType();
    $field_types_for_ief = [
      'entity_reference',
      'entity_reference_revisions',
    ];
    $target_type = $field_definition->getItemDefinition()->getSettings()['target_type'] ?? '';

    return (in_array($fieldType, $field_types_for_ief)) && ($target_type === "node");
  }

}
