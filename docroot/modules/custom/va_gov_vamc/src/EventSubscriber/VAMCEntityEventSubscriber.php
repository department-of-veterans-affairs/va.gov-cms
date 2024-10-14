<?php

namespace Drupal\va_gov_vamc\EventSubscriber;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\feature_toggle\FeatureStatus;
use Drupal\node\NodeInterface;
use Drupal\va_gov_notifications\Service\NotificationsManager;
use Drupal\va_gov_user\Service\UserPermsService;
use Drupal\va_gov_vamc\Service\ContentHardeningDeduper;
use Drupal\va_gov_workflow\Service\Flagger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
 */
class VAMCEntityEventSubscriber implements EventSubscriberInterface {

  // The UID of the CMS Help Desk account subscribing to facility messages.
  const USER_CMS_HELP_DESK_NOTIFICATIONS = 4050;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      'hook_event_dispatcher.form_node_vamc_operating_status_and_alerts_form.alter' => 'alterOpStatusNodeForm',
      'hook_event_dispatcher.form_node_full_width_banner_alert_edit_form.alter' => 'alterFullWidthBannerNodeForm',
      'hook_event_dispatcher.form_node_full_width_banner_alert_form.alter' => 'alterFullWidthBannerNodeForm',
      'hook_event_dispatcher.form_node_health_care_local_facility_edit_form.alter' => 'alterFacilityNodeForm',
      'hook_event_dispatcher.form_node_health_care_local_facility_form.alter' => 'alterFacilityNodeForm',
      'hook_event_dispatcher.form_node_regional_health_care_service_des_edit_form.alter' => 'alterRegionalHealthCareServiceDesNodeForm',
      'hook_event_dispatcher.form_node_regional_health_care_service_des_form.alter' => 'alterRegionalHealthCareServiceDesNodeForm',
      'hook_event_dispatcher.form_node_vamc_operating_status_and_alerts_edit_form.alter' => 'alterOpStatusNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_billing_insurance_edit_form.alter' => 'alterVamcSystemBillingAndInsuranceForm',
      'hook_event_dispatcher.form_node_vamc_system_billing_insurance_form.alter' => 'alterVamcSystemBillingAndInsuranceForm',
      'hook_event_dispatcher.form_node_vamc_system_medical_records_offi_edit_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_medical_records_offi_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_policies_page_edit_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_policies_page_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_register_for_care_edit_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_register_for_care_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_va_police_edit_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_va_police_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_health_care_local_health_service_edit_form.alter' => 'alterFacilityServiceNodeForm',
      EntityHookEvents::ENTITY_INSERT => 'entityInsert',
      EntityHookEvents::ENTITY_PRE_SAVE => 'entityPresave',
      EntityHookEvents::ENTITY_VIEW_ALTER => 'entityViewAlter',
      EntityHookEvents::ENTITY_UPDATE => 'entityUpdate',
    ];
  }

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   *  The entity manager.
   */
  private $entityTypeManager;

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
  protected $currentUser;


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
   * Feature Toggle status service.
   *
   * @var \Drupal\feature_toggle\FeatureStatus
   */
  private FeatureStatus $featureStatus;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The string entity type service.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   * @param \Drupal\va_gov_workflow\Service\Flagger $flagger
   *   The vagov workflow flagger service.
   * @param \Drupal\va_gov_user\Service\UserPermsService $user_perms_service
   *   The user perms service.
   * @param \Drupal\va_gov_vamc\Service\ContentHardeningDeduper $content_hardening_deduper
   *   The deduper service.
   * @param \Drupal\va_gov_notifications\Service\NotificationsManager $notifications_manager
   *   VA gov NotificationsManager service.
   * @param \Drupal\feature_toggle\FeatureStatus $feature_status
   *   The Feature Status service.
   */
  public function __construct(
    EntityTypeManager $entity_type_manager,
    AccountInterface $currentUser,
    Flagger $flagger,
    UserPermsService $user_perms_service,
    ContentHardeningDeduper $content_hardening_deduper,
    NotificationsManager $notifications_manager,
    FeatureStatus $feature_status,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $currentUser;
    $this->flagger = $flagger;
    $this->userPermsService = $user_perms_service;
    $this->contentHardeningDeduper = $content_hardening_deduper;
    $this->notificationsManager = $notifications_manager;
    $this->featureStatus = $feature_status;
  }

  /**
   * Alteration to entity view pages.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function entityViewAlter(EntityViewAlterEvent $event):void {
    $this->showUnspecifiedWhenSystemEhrNumberEmpty($event);
    $this->alterAppendedSystemHealthServices($event);
    $this->showRenderedTelephone($event);
  }

  /**
   * Show the correct telephone field based on feature toggle for VACMS-17854.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter event.
   */
  private function showRenderedTelephone(EntityViewAlterEvent $event) {
    $node_type = $event->getEntity()->bundle();
    if ($node_type !== 'vamc_system_billing_insurance' &&
        $node_type !== 'health_care_local_facility') {
      return;
    }
    // We want to hide the old mental health phone field on the facility node.
    $old_field_to_hide = $node_type === 'health_care_local_facility'
      ? 'field_mental_health_phone' : 'field_phone_number';

    $build = &$event->getBuild();
    $status = $this->featureStatus->getStatus('feature_telephone_migration_v1');
    if ($status) {
      // Hide the old telephone field, and, thereby, show the new one.
      unset($build[$old_field_to_hide]);
    }
    else {
      // Hide the new telephone field, and, thereby, show the old one.
      unset($build['field_telephone']);
    }
  }

  /**
   * Alters health service titles appended to VAMC system view page.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function alterAppendedSystemHealthServices(EntityViewAlterEvent $event):void {
    $display = $event->getDisplay();
    if (($display->getTargetBundle() === 'health_care_region_page') && ($display->getOriginalMode() === 'full')) {
      $build = &$event->getBuild();
      $services = $build['field_clinical_health_services'] ?? [];

      $services_copy = [];
      foreach ($services as $key => $service) {
        // If there are services (because their keys are numeric).
        if (is_numeric($key) && !empty($service['#options']['entity'])) {
          // Copy build array.
          $services_copy[] = $build['field_clinical_health_services'][$key];
          unset($build['field_clinical_health_services'][$key]);
          $service_node = $services_copy[$key]['#options']['entity'];
          $moderationState = $service_node->get('moderation_state')->value;
          // Identify archive and draft in temp array.
          if ($moderationState === 'archived' || $moderationState === 'draft') {
            $services_copy[$key]['#attributes'] = ['class' => 'node--unpublished'];
            $services_copy[$key]['#title'] .= ' (' . ucfirst($moderationState) . ')';
          }
        }
      }
      // Sort temp array.
      usort($services_copy, function ($x, $y) {
        return strcasecmp($x['#title'], $y['#title']);
      });
      // Copy temporary array back to build array.
      foreach ($services_copy as $key => $temp) {
        $build['field_clinical_health_services'][$key] = $services_copy[$key];
      }
      $build['field_clinical_health_services']['#attached']['library'][] = 'va_gov_vamc/set_vamc_system_health_service';
    }

  }

  /**
   * Entity presave Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event.
   */
  public function entityPresave(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    $this->contentHardeningDeduper->removeDuplicate($entity);
  }

  /**
   * Entity update Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event.
   */
  public function entityUpdate(EntityUpdateEvent $event): void {
    $entity = $event->getEntity();

    if ($this->isFlaggableFacility($entity)) {
      if ($entity->bundle() === 'vet_center') {
        $this->flagger->flagFieldChanged('field_official_name', 'changed_name', $entity, "The Official name of this facility changed from '@old' to '@new'.");
        $this->notificationsManager->sendMessageOnFieldChange('field_official_name', $entity, 'Vet Center Official Name Change:', 'vet_center_official_name_change', self::USER_CMS_HELP_DESK_NOTIFICATIONS);
      }
      else {
        $this->flagger->flagFieldChanged('title', 'changed_name', $entity, "The title of this facility changed from '@old' to '@new'.");
        $this->notificationsManager->sendMessageOnFieldChange('title', $entity, 'Facility title changed:', 'va_facility_title_change', self::USER_CMS_HELP_DESK_NOTIFICATIONS);
      }
    }
  }

  /**
   * Entity insert Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event.
   */
  public function entityInsert(EntityInsertEvent $event): void {
    $entity = $event->getEntity();

    if ($this->isFlaggableFacility($entity)) {
      $this->flagger->flagNew('new', $entity, "This facility is new and needs the 'new facility' runbook.");
      // Email the help desk when a new facility is created.
      $first_save = (empty($entity->original)) ? TRUE : FALSE;
      if (!(defined('IS_BEHAT') && IS_BEHAT) && ($entity->isNew() || $first_save)) {
        $message_fields = $this->notificationsManager->buildMessageFields($entity, 'New facility:');
        $this->notificationsManager->send('va_facility_new_facility', self::USER_CMS_HELP_DESK_NOTIFICATIONS, $message_fields);
      }
    }
  }

  /**
   * Tests to see if an object is a flaggable facility.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   One of many possible types of entity.
   *
   * @return bool
   *   TRUE if it is a flaggable facility. FALSE otherwise.
   */
  protected function isFlaggableFacility(EntityInterface $entity): bool {
    $flaggable_facilities = [
      'nca_facility',
      'vba_facility',
      'health_care_local_facility',
      'vet_center',
      'vet_center_mobile_vet_center',
      'vet_center_outstation',
    ];
    if ($entity instanceof NodeInterface && in_array($entity->bundle(), $flaggable_facilities)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Adds COVID status information to form and js library.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function addCovidStatusData(array &$form, FormStateInterface $form_state): void {
    /** @var \Drupal\taxonomy\TermStorage $term_storage */
    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $covid_status = [
      // Low.
      '1037',
      // Medium.
      '1036',
      // High.
      '1035',
    ];
    $terms_text = [];
    $chosen_term_description = "";
    /** @var \Drupal\Core\Entity\EntityFormInterface $form_object */
    $form_object = $form_state->getFormObject();
    $node = $form_object->getEntity();
    foreach ($covid_status as $status) {
      $terms_text[$status]['description'] = $term_storage->load($status)->getDescription();
      // If this is the chosen status (but there are no Details)
      // get the term description.
      if ($node->get('field_supplemental_status')->target_id === $status
          && empty($node->get('field_supplemental_status_more_i')->value)) {
        $chosen_term_description = $term_storage->load($status)->getDescription();
        // Populate the Details WYSIWYG with the term description.
        $form['field_supplemental_status_more_i']['widget'][0]['#default_value'] = $chosen_term_description;
      }
      $form['#attached']['library'][] = 'va_gov_vamc/set_covid_term_text';
      $form['#attached']['drupalSettings']['vamcCovidStatusTermText'] = $terms_text;
    }
  }

  /**
   * Removes the phone label on VAMC facility content type forms.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  private function removePhoneLabel(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form['field_telephone']['widget'][0]['subform']['field_phone_label']['#access'] = FALSE;
  }

  /**
   * Alter VAMC Facility node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterFacilityNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    $this->addCovidStatusData($form, $form_state);
    $this->removePhoneLabel($event);
    $this->showTelephone($event);
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
   * Alter the VAMC System and Billing Insurance node form..
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterVamcSystemBillingAndInsuranceForm(FormIdAlterEvent $event) {
    $this->alterTopTaskNodeForm($event);
    $this->removePhoneLabel($event);
    $this->showTelephone($event);
  }

  /**
   * Show the correct telephone field based on feature toggle for VACMS-17854.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The form event.
   */
  private function showTelephone($event) {
    $form = &$event->getForm();
    $form_id = $form['#form_id'];
    // We want to hide the old mental health phone field on the facility node,
    // where there are two phone fields.
    $old_field_to_hide = $form_id === 'node_health_care_local_facility_form' || $form_id === 'node_health_care_local_facility_edit_form'
      ? 'field_mental_health_phone' : 'field_phone_number';
    $status = $this->featureStatus->getStatus('feature_telephone_migration_v1');
    if ($status) {
      // Hide the old telephone field, and, thereby, show the new one.
      unset($form[$old_field_to_hide]);
    }
    else {
      // Hide the new telephone field, and, thereby, show the old one.
      unset($form['field_telephone']);
    }
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
   * Add js script and disallowed nids to VAMC System Health Service node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterRegionalHealthCareServiceDesNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $vamc_field_options = $form['field_region_page']['widget']['#options'];
    foreach ($vamc_field_options as $nid => $node_option_string) {
      $perms = $this->userPermsService->userAccess($nid, 'node', $this->currentUser, 'field_office');
      if (!$perms) {
        $form['#attached']['drupalSettings']['va_gov_vamc']['disallowed_vamc_options'][] = $nid;
      }
    }
    $form['#attached']['library'][] = 'va_gov_vamc/limit_vamcs_to_workbench';
  }

  /**
   * Alter the VAMC Facility Service node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterFacilityServiceNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    $is_admin = $this->userPermsService->hasAdminRole(TRUE);
    if (!$is_admin) {
      $this->disableFacilityServiceChange($form, $form_state);
    }
  }

  /**
   * Disables basic info fields on existing nodes for editors.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   */
  public function disableFacilityServiceChange(array &$form, FormStateInterface $form_state): void {
    /** @var \Drupal\Core\Entity\EntityFormInterface $form_object */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\node\NodeInterface $node */
    $node = $form_object->getEntity();
    if (!$node->isNew()) {
      $form['field_facility_location']['#disabled'] = TRUE;
      $form['field_regional_health_service']['#disabled'] = TRUE;
    }
  }

  /**
   * Clear custom appointment intro text when unused.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   */
  protected function clearCustomAppointmentIntroText(EntityInterface $entity): void {
    if ($entity instanceof NodeInterface) {
      $bundle = $entity->bundle();
      /** @var \Drupal\node\NodeInterface $entity */
      if (($bundle === 'health_care_local_health_service')
      && ($entity->hasField('field_hservice_appt_intro_select'))
      && ($entity->hasField('field_hservice_appt_leadin'))) {
        $appt_select = $entity->get('field_hservice_appt_intro_select')->value;
        $appt_leadin = $entity->get('field_hservice_appt_leadin')->value;
        if ($appt_select !== 'custom_intro_text' && !empty($appt_leadin)) {
          $entity->set('field_hservice_appt_leadin', '');
        }
      }
    }
  }

  /**
   * Clear service location hours when unused.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   */
  protected function clearUnusedServiceLocationHours(EntityInterface $entity): void {
    if ($entity instanceof ParagraphInterface) {
      $type = $entity->getType();
      /** @var \Drupal\paragraphs\ParagraphInterface $entity */
      if (($type === 'service_location')
      && ($entity->hasField('field_hours'))
      && ($entity->hasField('field_office_hours'))) {
        $hours = $entity->get('field_hours')->value;
        $office_hours = $entity->get('field_office_hours')->getValue();
        // 2 = Provide specific hours for this service.
        if ($hours !== '2' && count($office_hours) > 0) {
          $entity->set('field_office_hours', []);
        }
      }
    }
  }

  /**
   * Shows the text "Unspecified" when phone number is blank.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityViewAlterEvent $event
   *   The entity view alter service.
   */
  public function showUnspecifiedWhenSystemEhrNumberEmpty(EntityViewAlterEvent $event):void {
    if ($event->getDisplay()->getTargetBundle() === 'health_care_region_page') {
      $build = &$event->getBuild();
      if (empty($build['field_va_health_connect_phone']['#title'])) {
        $undefined_number_text = '
          <div class="field field--name-field-va-health-connect-phone field--type-list-string field--label-above">
              <div class="field__label">VA Health Connect phone number</div>
              <div class="field__item">Undefined</div>
          </div>';

        $formatted_markup = new FormattableMarkup($undefined_number_text, []);
        $build['field_va_health_connect_phone']['#prefix'] = $formatted_markup;
      }
    }

  }

}
