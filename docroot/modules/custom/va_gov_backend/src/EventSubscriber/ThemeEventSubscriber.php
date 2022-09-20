<?php

namespace Drupal\va_gov_backend\EventSubscriber;

use Drupal\Core\Render\Element;
use Drupal\core_event_dispatcher\Event\Theme\ThemeSuggestionsAlterEvent;
use Drupal\core_event_dispatcher\ThemeHookEvents;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\field_event_dispatcher\Event\Field\WidgetSingleElementFormAlterEvent;
use Drupal\field_event_dispatcher\FieldHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * VA.gov Theme Event Subscriber.
 */
class ThemeEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      FieldHookEvents::WIDGET_SINGLE_ELEMENT_FORM_ALTER => 'formWidgetAlter',
      FormHookEvents::FORM_ALTER                        => 'formAlter',
      ThemeHookEvents::THEME_SUGGESTIONS_ALTER          => 'themeSuggestionsAlter',
    ];
  }

  /**
   * Widget form alter Event call.
   *
   * @param \Drupal\field_event_dispatcher\Event\Field\WidgetSingleElementFormAlterEvent $event
   *   The event.
   */
  public function formWidgetAlter(WidgetSingleElementFormAlterEvent $event): void {
    $element = $event->getElement();
    $form_state = $event->getFormState();
    $context = $event->getContext();

  }

  /**
   * Form alter Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The event.
   */
  public function formAlter(FormAlterEvent $event): void {
    $form    = &$event->getForm();
    $form_id = $event->getFormId();
    $this->attachFormId($form, $form_id);
  }

  /**
   * Attaches form id to all form elements.
   *
   * @param array $form
   *   The form or form element which children should have form id attached.
   * @param string $form_id
   *   The form id attached to form elements.
   */
  protected function attachFormId(array &$form, string $form_id) {
    foreach (Element::children($form) as $child) {
      if (!isset($form[$child]['#form_id'])) {
        $form[$child]['#form_id'] = $form_id;
      }
      // Recurse for children.
      $this->attachFormId($form[$child], $form_id);
    }
  }

  /**
   * Theme suggestions alter Event call.
   *
   * @param \Drupal\core_event_dispatcher\Event\Theme\ThemeSuggestionsAlterEvent $event
   *   The event.
   */
  public function themeSuggestionsAlter(ThemeSuggestionsAlterEvent $event) {
    $suggestions = &$event->getSuggestions();
    $variables   = $event->getVariables();
    $hook        = $event->getHook();
    if (
      isset($variables['element']['#form_id'])
      && isset($variables['element']['#type'])
      && isset($variables['element']['#name'])
    ) {
      $element       = $variables['element'];
      $formid        = str_replace('-', '_', $element['#form_id']);
      $suggestions[] = $hook . '__' . $formid;
      $suggestions[] = $hook . '__' . $element['#type'] . '__' . $formid;
      $suggestions[] = $hook . '__' . $element['#name'] . '__' . $formid;
      $suggestions[] = $hook . '__' . $element['#name'] . '__' . $element['#type'] . '__' . $formid;
    }
  }

}
