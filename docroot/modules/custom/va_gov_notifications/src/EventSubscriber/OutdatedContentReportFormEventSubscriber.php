<?php

namespace Drupal\va_gov_notifications\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OutdatedContentReportFormEventSubscriber.
 *
 * Don't forget to define your class as a service and tag it as
 * an "event_subscriber":
 *
 * services:
 *  hook_event_dispatcher.example_form_subscribers:
 *   class: Drupal\hook_event_dispatcher\ExampleFormEventSubscribers
 *   tags:
 *     - { name: event_subscriber }
 */
class OutdatedContentReportFormEventSubscriber implements EventSubscriberInterface {

  /**
   * Form alter Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The event.
   */
  public function formAlter(FormAlterEvent $event): void {
    $form = &$event->getForm();
    $form_id = $event->getFormId();

    if ($form_id === 'views_exposed_form' && $form['#id'] === 'views-exposed-form-content-outdated-content') {
      $form['field_last_saved_by_an_editor_value_1'] += ['#attributes' => ['class' => ['visually-hidden']]];
      $debug = $form;
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      FormHookEvents::FORM_ALTER => 'formAlter',
    ];
  }

}
