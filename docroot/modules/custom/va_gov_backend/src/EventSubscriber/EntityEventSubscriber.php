<?php

namespace Drupal\va_gov_backend\EventSubscriber;

use Drupal\node\NodeInterface;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  /**
   * Entity presave Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event.
   */
  public function entityPresave(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface) {
      $this->trimNodeTitleWhitespace($entity);
    }
  }

  /**
   * Trim any preceding and trailing whitespace on node titles.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to be modified.
   */
  private function trimNodeTitleWhitespace(NodeInterface $node) {
    $title = $node->getTitle();
    // Trim leading and then trailing separately to avoid a convoluted regex.
    $title = preg_replace('/^\s+/', '', $title);
    $title = preg_replace('/\s+$/', '', $title);
    $node->setTitle($title);
  }

  /**
   * Form alterations for staff profile content type.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterstaffProfileNodeForm(FormIdAlterEvent $event): void {
    $this->addStateManagementToBioFields($event);
  }

  /**
   * Add states management to bio fields to determine visibility based on bool.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function addStateManagementToBioFields(FormIdAlterEvent $event) {
    $form = &$event->getForm();
    $form['#submit'][] = $this->addStateManagementToBioFieldsSubmitHandler($event);
    $form['#attached']['library'][] = 'va_gov_backend/set_body_to_required';
    $selector = ':input[name="field_complete_biography_create[value]"]';
    $form['field_intro_text']['widget'][0]['value']['#states'] = [
      'required' => [
          [$selector => ['checked' => TRUE]],
      ],
      'visible' => [
            [$selector => ['checked' => TRUE]],
      ],
    ];
    $form['field_body']['#states'] = [
      'visible' => [
          [$selector => ['checked' => TRUE]],
      ],
    ];
    $form['field_complete_biography']['#states'] = [
      'visible' => [
          [$selector => ['checked' => TRUE]],
      ],
    ];
  }

  /**
   * Submit handler removes field body req when bio toggle is set to FALSE.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function addStateManagementToBioFieldsSubmitHandler(FormIdAlterEvent $event) {
    $form = &$event->getForm();
    $form_state = $event->getFormState();
    $bio_display = !empty($form_state->getUserInput()['field_complete_biography_create']['value']) ? TRUE : FALSE;
    if (!$bio_display) {
      $form['field_body']['widget'][0]['#required'] = FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'entityPresave',
      // React on staff profile forms.
      'hook_event_dispatcher.form_node_person_profile_form.alter' => 'alterStaffProfileNodeForm',
      'hook_event_dispatcher.form_node_person_profile_edit_form.alter' => 'alterStaffProfileNodeForm',
    ];
  }

}
