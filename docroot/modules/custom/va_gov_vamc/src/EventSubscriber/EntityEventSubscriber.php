<?php

namespace Drupal\va_gov_vamc\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\va_gov_notifications\Service\NotificationsManager;
use Drupal\va_gov_user\Service\UserPermsService;
use Drupal\va_gov_vamc\Service\ContentHardeningDeduper;
use Drupal\va_gov_workflow\Service\Flagger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // React on Op status forms.
      'hook_event_dispatcher.form_node_vamc_operating_status_and_alerts_form.alter' => 'alterOpStatusNodeForm',
      'hook_event_dispatcher.form_node_vamc_operating_status_and_alerts_edit_form.alter' => 'alterOpStatusNodeForm',
      EntityHookEvents::ENTITY_INSERT => 'entityInsert',
      EntityHookEvents::ENTITY_PRE_SAVE => 'entityPresave',
      EntityHookEvents::ENTITY_UPDATE => 'entityUpdate',
      'hook_event_dispatcher.form_node_full_width_banner_alert_form.alter' => 'alterFullWidthBannerNodeForm',
      'hook_event_dispatcher.form_node_full_width_banner_alert_edit_form.alter' => 'alterFullWidthBannerNodeForm',
      'hook_event_dispatcher.form_node_regional_health_care_service_des_form.alter' => 'alterRegionalHealthCareServiceDesNodeForm',
      'hook_event_dispatcher.form_node_regional_health_care_service_des_edit_form.alter' => 'alterRegionalHealthCareServiceDesNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_register_for_care_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_register_for_care_edit_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_medical_records_offi_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_medical_records_offi_edit_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_billing_insurance_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_billing_insurance_edit_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_policies_page_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_policies_page_edit_form.alter' => 'alterTopTaskNodeForm',
    ];
  }

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
   * Constructs the EventSubscriber object.
   *
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
   */
  public function __construct(
    AccountInterface $currentUser,
    Flagger $flagger,
    UserPermsService $user_perms_service,
    ContentHardeningDeduper $content_hardening_deduper,
    NotificationsManager $notifications_manager
  ) {
    $this->currentUser = $currentUser;
    $this->flagger = $flagger;
    $this->userPermsService = $user_perms_service;
    $this->contentHardeningDeduper = $content_hardening_deduper;
    $this->notificationsManager = $notifications_manager;
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
      }
      else {
        $this->flagger->flagFieldChanged('title', 'changed_name', $entity, "The title of this facility changed from '@old' to '@new'.");
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
      // @codingStandardsIgnoreStart
      // Sample code for building a message notification. Using swirt's user id
      // for now.
      // $message_fields = $this->notificationsManager->buildMessageFields($entity, 'New facility:');
      // $this->notificationsManager->send('va_facility_new_facility', 1215, $message_fields);
      // @codingStandardsIgnoreEnd
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

}
