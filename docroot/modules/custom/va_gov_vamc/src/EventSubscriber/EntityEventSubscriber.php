<?php

namespace Drupal\va_gov_vamc\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\va_gov_user\Service\UserPermsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
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
   * Add js script and disallowed nids to Banner node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterBannerNodeForm(FormIdAlterEvent $event): void {
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
      // React on banner forms.
      'hook_event_dispatcher.form_node_full_width_banner_alert_form.alter' => 'alterBannerNodeForm',
      'hook_event_dispatcher.form_node_full_width_banner_alert_edit_form.alter' => 'alterBannerNodeForm',
    ];
  }

}
