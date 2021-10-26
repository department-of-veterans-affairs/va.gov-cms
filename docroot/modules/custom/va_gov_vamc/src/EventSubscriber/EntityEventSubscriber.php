<?php

namespace Drupal\va_gov_vamc\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\va_gov_user\Service\UserPermsService;
use Drupal\va_gov_vamc\Service\ContentHardeningDeduper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {
  /**
   * The content hardening deduper.
   *
   * @var \Drupal\va_gov_vamc\Service\ContentHardeningDeduper
   *  The content hardening deduper service.
   */
  private $contentHardeningDeduper;

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
   * @param \Drupal\va_gov_vamc\Service\ContentHardeningDeduper $content_hardening_deduper
   *   The deduper service.
   */
  public function __construct(
    AccountInterface $currentUser,
    UserPermsService $user_perms_service,
    ContentHardeningDeduper $content_hardening_deduper
  ) {
    $this->currentUser = $currentUser;
    $this->userPermsService = $user_perms_service;
    $this->contentHardeningDeduper = $content_hardening_deduper;
  }

  /**
   * Entity create Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event.
   */
  public function entityPresave(EntityPresaveEvent $event): void {
    // Do some fancy stuff with new entity.
    $entity = $event->getEntity();
    $this->contentHardeningDeduper->removeDuplicate($entity);
  }

  /**
   * Entity view alter Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The event.
   */
  public function alterEntityView(EntityViewAlterEvent $event): void {
    $entity = $event->getEntity();
    $build = &$event->getBuild();
    $this->unsetFacilityServiceFields($build, $entity);
  }

  /**
   * Unsets the fields that need to be unset.
   *
   * @param array $build
   *   The assembled entity array.
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity.
   */
  public function unsetFacilityServiceFields(array &$build, FieldableEntityInterface $entity) {
    if ($entity instanceof FieldableEntityInterface && $entity->getEntityTypeId() === 'node' && $entity->bundle() === 'health_care_local_health_service') {
      $appt_intro_select = $entity->get('field_hservice_appt_intro_select')->value;
      if ($appt_intro_select === 'default_intro_text') {
        unset($build['field_hservice_appt_leadin']);
      }
      elseif ($appt_intro_select === 'custom_intro_text') {
        unset($build['field_hservices_lead_in_default']);
      }
      else {
        unset($build['field_hservice_appt_leadin']);
        unset($build['field_hservices_lead_in_default']);
      }
    }
  }

  /**
   * Add js script to VAMC Op status node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterOpStatusNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form['#attached']['library'][] = 'va_gov_vamc/set_ief_administration_select';
  }

  /**
   * Add js script to VAMC Op status node form.
   *
   * Hide field_office for existing operating statuses.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterOpStatusNodeEditForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form['#attached']['library'][] = 'va_gov_vamc/set_ief_administration_select';
    $form['field_office']['#attributes']['class'] = 'hidden';
  }

  /**
   * Add js script for menu title setting.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterTopTaskNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form['#attached']['library'][] = 'va_gov_vamc/set_menu_title';
    $form['title']['#disabled'] = 'disabled';
  }

  /**
   * Add js script and disallowed nids to Full Width Banner node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterFullWidthBannerNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $vamc_field_options = $form['field_banner_alert_vamcs']['widget']['#options'];
    foreach ($vamc_field_options as $nid => $node_option_string) {
      $perms = $this->userPermsService->userAccess($nid, 'node', $this->currentUser, 'field_office');
      if (!$perms) {
        $form['#attached']['drupalSettings']['va_gov_vamc']['disallowed_vamc_options'][] = $nid;
      }
    }
    $form['#attached']['library'][] = 'va_gov_vamc/limit_vamcs_to_workbench';
  }

  /**
   * Alters Health care region page node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterHealthCareRegionPageNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $this->restrictTitleAccess($form);
  }

  /**
   * Determines whether or not user can edit title.
   *
   * @param array $form
   *   The node form array.
   */
  public function restrictTitleAccess(array &$form) {
    if (!$this->userPermsService->hasAdminRole()) {
      $form['title']['#disabled'] = 'disabled';
    }
  }

  /**
   * Alters Health care local service node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterHealthCareLocalServiceNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    // Attach the service options limiter.
    $form['#attached']['library'][] = 'va_gov_vamc/limit_services_to_user_sections';
    $this->modifyApptIntroTextStates($form, $form_state);
  }

  /**
   * Visibility of Appointment Lead-in Text field.
   *
   * @param array $form
   *   The node form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Instance of FormStateInterface.
   */
  public function modifyApptIntroTextStates(array &$form, FormStateInterface $form_state) {
    $form['field_hservice_appt_leadin']['widget'][0]['value']['#title_display'] = 'invisible';
    if (isset($form['field_hservice_appt_intro_select'])) {
      $form['field_hservice_appt_leadin']['#states'] = [
        'visible' => [
          ':input[name="field_hservice_appt_intro_select"]' => ['value' => 'custom_intro_text'],
        ],
      ];
      $form['field_hservices_lead_in_default']['#states'] = [
        'visible' => [
          ':input[name="field_hservice_appt_intro_select"]' => ['value' => 'default_intro_text'],
        ],
      ];
    }
    $form['#validate'][] = $this->apptIntroTextValidation($form, $form_state);
  }

  /**
   * Adds Validation to check for an empty Appointment lead-in field.
   *
   * @param array $form
   *   The exposed widget form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function apptIntroTextValidation(array $form, FormStateInterface $form_state) {
    if (!empty($form_state->getValue('field_hservice_appt_leadin')) && !empty($form_state->getValue('field_hservice_appt_intro_select'))) {
      $intro_type = $form_state->getValue('field_hservice_appt_intro_select');
      $intro_text_value = $form_state->getValue('field_hservice_appt_leadin');
      if ($intro_type[0]['value'] === 'custom_intro_text') {
        if (($intro_text_value[0]['value'] === "")) {
          $form_state->setErrorByName("field_hservice_appt_leadin", t('Appointment lead-in text required'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'entityPresave',
      HookEventDispatcherInterface::ENTITY_VIEW_ALTER => 'alterEntityView',
      'hook_event_dispatcher.form_node_vamc_operating_status_and_alerts_form.alter' => 'alterOpStatusNodeForm',
      'hook_event_dispatcher.form_node_vamc_operating_status_and_alerts_edit_form.alter' => 'alterOpStatusNodeEditForm',
      'hook_event_dispatcher.form_node_full_width_banner_alert_form.alter' => 'alterFullWidthBannerNodeForm',
      'hook_event_dispatcher.form_node_full_width_banner_alert_edit_form.alter' => 'alterFullWidthBannerNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_register_for_care_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_register_for_care_edit_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_medical_records_offi_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_medical_records_offi_edit_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_billing_insurance_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_billing_insurance_edit_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_policies_page_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_policies_page_edit_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_health_care_region_page_edit_form.alter' => 'alterHealthCareRegionPageNodeForm',
      'hook_event_dispatcher.form_health_care_local_health_service_form.alter' => 'alterHealthCareLocalServiceNodeForm',
      'hook_event_dispatcher.form_health_care_local_health_service_edit_form.alter' => 'alterHealthCareLocalServiceNodeForm',
    ];
  }

}
