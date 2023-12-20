<?php

namespace Drupal\va_gov_views\EventSubscriber;

use Drupal\Core\Form\FormStateInterface;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA Gov Views event subscriber.
 */
class ViewsBulkOpsSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      FormHookEvents::FORM_ALTER => 'formAlter',
    ];
  }

  /**
   * Form alter Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The event.
   */
  public function formAlter(FormAlterEvent $event): void {
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    $this->viewsFormContentPageTwoAlter($form, $form_state);
  }

  /**
   * Alter the views form for content_page_2.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  private function viewsFormContentPageTwoAlter(array &$form, FormStateInterface $form_state): void {
    $id = $form_state->getFormObject()->getFormId();

    if ($id === 'views_form_content_page_2') {
      $form['header']['views_bulk_operations_bulk_form_1']['action']['#weight'] = 1;
      $form['header']['views_bulk_operations_bulk_form_1']['select_all']['#weight'] = 2;
      $form['header']['views_bulk_operations_bulk_form_1']['multipage']['#weight'] = 3;
      $form['header']['views_bulk_operations_bulk_form_1']['actions']['#weight'] = 4;
    }
  }

}
