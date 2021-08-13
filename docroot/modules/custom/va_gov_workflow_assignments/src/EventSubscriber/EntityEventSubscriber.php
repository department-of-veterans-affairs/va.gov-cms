<?php

namespace Drupal\va_gov_workflow_assignments\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Workflow Assignments Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  /**
   * Set alert block edit form status default to draft.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterAlertBlocksEditForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $form['moderation_state']['widget'][0]['state']['#default_value'] = 'draft';
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // React on alert block edit forms.
      'hook_event_dispatcher.form_block_content_alert_edit_form.alter' => 'alterAlertBlocksEditForm',
    ];
  }

}
