<?php

namespace Drupal\va_gov_backend\EventSubscriber;

use Drupal\Core\Form\FormStateInterface;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
 */
class LastEditorSaveEventSubscriber implements EventSubscriberInterface {

  /**
   * Form alter Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The event.
   */
  public function formAlter(FormAlterEvent $event): void {
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    $form_id = $event->getFormId();

    $base_form_id = $form_state->getBuildInfo()['base_form_id'] ?? '';
    if ($base_form_id === 'node_form') {
      $form['field_last_saved_by_an_editor']['#access'] = FALSE;
      $form['actions']['submit']['#submit'][] = [
        $this, 'lastSavedByEditorSetTimestamp',
      ];
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

  /**
   * Custom form submit to set the value for the last saved by editor field.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function lastSavedByEditorSetTimestamp(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $form_state->getFormObject()->getEntity();
    $timestamp = time();
    $node->set('field_last_saved_by_an_editor', $timestamp);
  }

}
