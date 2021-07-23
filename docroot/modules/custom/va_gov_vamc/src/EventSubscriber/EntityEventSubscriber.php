<?php

namespace Drupal\va_gov_vamc\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

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
   * Add js script to Banner node form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterBannerNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
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
