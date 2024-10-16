<?php

namespace Drupal\va_gov_profile\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityTypeAlterEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\feature_toggle\FeatureStatus;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov person_profile entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The User Perms Service.
   *
   * @var \Drupal\va_gov_user\Service\UserPermsService
   */
  protected $userPermsService;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * Feature Toggle status service.
   *
   * @var \Drupal\feature_toggle\FeatureStatus
   */
  private FeatureStatus $featureStatus;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The current user perms service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\feature_toggle\FeatureStatus $feature_status
   *   The Feature Status service.
   */
  public function __construct(
    UserPermsService $user_perms_service,
    EntityTypeManager $entity_type_manager,
    FeatureStatus $feature_status,
  ) {
    $this->userPermsService = $user_perms_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->featureStatus = $feature_status;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      'hook_event_dispatcher.form_node_person_profile_edit_form.alter' => 'alterStaffProfileNodeForm',
      'hook_event_dispatcher.form_node_person_profile_form.alter' => 'alterStaffProfileNodeForm',
      EntityHookEvents::ENTITY_TYPE_ALTER => 'entityTypeAlter',
      EntityHookEvents::ENTITY_VIEW_ALTER => 'entityViewAlter',
    ];
  }

  /**
   * Equivalent of hook_entity_type_alter().
   *
   * @param Drupal\core_event_dispatcher\Event\Entity\EntityTypeAlterEvent $event
   *   The event for entyTypeAlter.
   */
  public function entityTypeAlter(EntityTypeAlterEvent $event): void {
    $entity_types = $event->getEntityTypes();
    if (!empty($entity_types['node'])) {
      $entity = $entity_types['node'];
      $entity->addConstraint('PersonPageRequiredFieldsConstraint');
    }
  }

  /**
   * Alteration to entity view pages.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function entityViewAlter(EntityViewAlterEvent $event):void {
    $this->showRenderedTelephone($event);
  }

  /**
   * Show the correct telephone field based on feature toggle for VACMS-17854.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter event.
   */
  private function showRenderedTelephone(EntityViewAlterEvent $event) {
    if ($event->getDisplay()->getTargetBundle() !== 'person_profile') {
      return;
    }
    $build = &$event->getBuild();
    $status = $this->featureStatus->getStatus('feature_telephone_migration_v1');
    if ($status) {
      // Hide the old telephone field, and, thereby, show the new one.
      unset($build['field_phone_number']);
    }
    else {
      // Hide the new telephone field, and, thereby, show the old one.
      unset($build['field_telephone']);
    }
  }

  /**
   * Form alterations for staff profile content type.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterstaffProfileNodeForm(FormIdAlterEvent $event): void {
    $this->addStateManagementToBioFields($event);
    $this->removePhoneLabel($event);
    $this->showTelephone($event);
  }

  /**
   * Add states management to bio fields to determine visibility based on bool.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function addStateManagementToBioFields(FormIdAlterEvent $event) {
    $form = &$event->getForm();
    $form['#attached']['library'][] = 'va_gov_profile/set_body_to_required';
    $selector = ':input[name="field_complete_biography_create[value]"]';
    $form['field_intro_text']['widget'][0]['value']['#states'] = [
      'required' => [
        [$selector => ['checked' => TRUE]],
      ],
      'visible' => [
        [$selector => ['checked' => TRUE]],
      ],
    ];

    $form['field_body']['widget'][0]['#states'] = [
      'visible' => [
        [$selector => ['checked' => TRUE]],
      ],
      // Unfortunately we can not set the requiredness of a ckeditor field using
      // states.  So we end up adding this with JS to bypass HTML5 validation
      // and let the validation constraint handle it.
      // This is to prevent the error:
      // An invalid form control with name='field_body[0][value]' is not
      // focusable.
      // because ckeditor changes the id of the field, so when html5 validation
      // kicks in, it can't find the field to hilight as being required.
      // @see https://www.drupal.org/project/drupal/issues/2722319
      // 'required' => [[$selector => ['checked' => TRUE]],],
    ];

    $form['field_body']['#states'] = [
      'visible' => [
        [$selector => ['checked' => TRUE]],
      ],
    ];

    $form['field_complete_biography']['#states'] = [
      'visible' => [
        [$selector => ['checked' => TRUE]],
      ],
    ];
  }

  /**
   * Show the correct telephone field based on feature toggle for VACMS-17854.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The form event.
   */
  private function showTelephone($event) {
    $form = &$event->getForm();
    $status = $this->featureStatus->getStatus('feature_telephone_migration_v1');
    if ($status) {
      // Hide the old telephone field, and, thereby, show the new one.
      unset($form['field_phone_number']);
    }
    else {
      // Hide the new telephone field, and, thereby, show the old one.
      unset($form['field_telephone']);
    }
  }

  /**
   * Removes the phone label on staff profile content type forms.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The form event.
   */
  private function removePhoneLabel(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form['field_telephone']['widget'][0]['subform']['field_phone_label']['#access'] = FALSE;
  }

}
