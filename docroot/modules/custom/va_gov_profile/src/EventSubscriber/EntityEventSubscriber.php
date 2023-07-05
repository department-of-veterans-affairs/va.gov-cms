<?php

namespace Drupal\va_gov_profile\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Generic Entity Event Subscriber. Do only multi-product stuff here.
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
    ];
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
    $form_state = $event->getFormState();
    // $this->addStateManagementToBioFieldsSubmitHandler($form, $form_state);
    // $form['#submit'][] = 'Drupal\va_gov_backend\EventSubscriber\EntityEventSubscriber::addStateManagementToBioFieldsSubmitHandler';
    // $form['#submit'][] = [$this, 'addStateManagementToBioFieldsSubmitHandler'];
    $form['#attached']['library'][] = 'va_gov_backend/set_body_to_required';
    $selector = ':input[name="field_complete_biography_create[value]"]';
    $form['field_intro_text']['widget'][0]['value']['#states'] = [
      'required' => [
          [$selector => ['checked' => TRUE]],
      ],
      'visible' => [
            [$selector => ['checked' => TRUE]],
      ],
    ];

    // $form['field_body']['widget']['value']['#states'] = [
    //   'required' => [
    //       [$selector => ['checked' => TRUE]],
    //   ],
    // ];
    // Error: An invalid form control with name='field_body[0][value]' is not
    // focusable.
    // This is the one that sets it right.  but fails validation because
    // ckeditor changes the id of the field, so when html 5 validation goes to
    // kick in, it can't find the field to hilight as being required.
    $form['field_body']['widget'][0]['#states'] = [
      'required' => [
          [$selector => ['checked' => TRUE]],
      ],
      'visible' => [
            [$selector => ['checked' => TRUE]],
      ],
    ];
    // This seems odd.... but useful.
    //  $form['actions']['submit']['#limit_validation_errors'] = [['revision_log'], ['field_name_first'], ['field_last_name'], ['field_administration'], ['field_office']];
    // $form['field_body']['widget'][0]['value']['#states'] = [
    //   'required' => [
    //       [$selector => ['checked' => TRUE]],
    //   ],
    // ];
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
   * Submit handler for DANSE forms.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function addStateManagementToBioFieldsSubmitHandler(array &$form, FormStateInterface $form_state) {
    $bio_display = !empty($form_state->getUserInput()['field_complete_biography_create']['value']) ? TRUE : FALSE;
    if (!$bio_display) {

      $form['field_intro_text']['widget']['#required'] = FALSE;
      $form['field_intro_text']['widget'][0]['#required'] = FALSE;
      $form['field_intro_text']['widget'][0]['value']['#required'] = FALSE;
      $form['field_body']['widget']['#required'] = FALSE;
      $form['field_body']['widget'][0]['#required'] = FALSE;
      $form['field_body']['widget'][0]['value']['#required'] = FALSE;
      // '#limit_validation_errors' => [],
    }
  }

}
