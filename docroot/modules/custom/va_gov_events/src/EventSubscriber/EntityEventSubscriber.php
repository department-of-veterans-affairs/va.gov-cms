<?php

namespace Drupal\va_gov_events\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov VAMC Entity Event Subscriber.
 */
class EntityEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Constructs the EventSubscriber object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(TranslationInterface $string_translation) {
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      'hook_event_dispatcher.form_node_event_form.alter' => 'alterEventNodeForm',
      'hook_event_dispatcher.form_node_event_edit_form.alter' => 'alterEventNodeForm',
    ];
  }

  /**
   * Form alterations for eventcontent type.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormIdAlterEvent $event
   *   The event.
   */
  public function alterEventNodeForm(FormIdAlterEvent $event): void {
    $form = &$event->getForm();
    $this->addDisplayManagementToEventFields($form);
    $this->modifyFormFieldElements($form);
  }

  /**
   * Show fields depending on value of checkbox.
   *
   * @param array $form
   *   The form.
   */
  public function addDisplayManagementToEventFields(array &$form) {
    $form['#attached']['library'][] = 'va_gov_events/event_form_states_helpers';
  }

  /**
   * Add prefix to cta button.
   *
   * Simplify address widget appearance.
   *
   * Replace linkit module help text with config help text.
   *
   * @param array $form
   *   The form.
   */
  public function modifyFormFieldElements(array &$form) {
    // Prefix our registration button.
    $form['field_event_cta']['widget']['#prefix'] = '<strong>' . $this->t('Registration button') . '</strong>';
    // Remove the wrap and title around address widget.
    $form['field_address']['widget'][0]['#type'] = 'div';
    unset($form['field_address']['widget'][0]['#title']);
    // Use help text from config instead of linkit module.
    $form['field_url_of_an_online_event']['widget'][0]['uri']['#description'] = $form['field_url_of_an_online_event']['widget'][0]['#description']->__toString();
  }

}
