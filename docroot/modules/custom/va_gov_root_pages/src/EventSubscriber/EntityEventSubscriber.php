<?php

namespace Drupal\va_gov_root_pages\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Root pages Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  /**
   * Add js script for menu title setting.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterTopTaskNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form['#attached']['library'][] = 'va_gov_root_pages/set_menu_title';
    $form['title']['#disabled'] = 'disabled';
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // React on top task forms.
      'hook_event_dispatcher.form_node_vamc_system_register_for_care_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_register_for_care_edit_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_medical_records_offi_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_medical_records_offi_edit_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_billing_insurance_form.alter' => 'alterTopTaskNodeForm',
      'hook_event_dispatcher.form_node_vamc_system_billing_insurance_edit_form.alter' => 'alterTopTaskNodeForm',
    ];
  }

}
