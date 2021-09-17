<?php

namespace Drupal\va_gov_vamc\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
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
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // React on Op status forms.
      'hook_event_dispatcher.form_node_vamc_operating_status_and_alerts_form.alter' => 'alterOpStatusNodeForm',
      'hook_event_dispatcher.form_node_vamc_operating_status_and_alerts_edit_form.alter' => 'alterOpStatusNodeForm',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'entityPresave',
      // React on full width banner forms.
      'hook_event_dispatcher.form_node_full_width_banner_alert_form.alter' => 'alterFullWidthBannerNodeForm',
      'hook_event_dispatcher.form_node_full_width_banner_alert_edit_form.alter' => 'alterFullWidthBannerNodeForm',
      // React on top task forms.
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

}
