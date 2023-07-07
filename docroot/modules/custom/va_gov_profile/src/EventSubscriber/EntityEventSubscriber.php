<?php

namespace Drupal\va_gov_profile\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityTypeAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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
   *  The entity manager.
   */
  private $entityTypeManager;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The current user perms service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(
    UserPermsService $user_perms_service,
    EntityTypeManager $entity_type_manager,
  ) {
    $this->userPermsService = $user_perms_service;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      'hook_event_dispatcher.form_node_person_profile_edit_form.alter' => 'alterStaffProfileNodeForm',
      'hook_event_dispatcher.form_node_person_profile_form.alter' => 'alterStaffProfileNodeForm',
      EntityHookEvents::ENTITY_TYPE_ALTER => 'entityTypeAlter',
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
   * Form alterations for staff profile content type.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterstaffProfileNodeForm(FormIdAlterEvent $event): void {
    $this->addStateManagementToBioFields($event);
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

}
