<?php

namespace Drupal\va_gov_backend\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field_event_dispatcher\Event\Field\FieldWidgetThirdPartySettingsFormEvent;
use Drupal\field_event_dispatcher\Event\Field\WidgetSingleElementTypeFormAlterEvent;
use Drupal\field_event_dispatcher\FieldHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for field hooks.
 */
class FieldSelectAllOptionsSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Subscriber for hook_field_widget_third_party_settings_form().
   *
   * @param \Drupal\field_event_dispatcher\Event\Field\FieldWidgetThirdPartySettingsFormEvent $event
   *   The third party settings form event.
   */
  public function alterFieldWidgetThirdPartySettingsForm(FieldWidgetThirdPartySettingsFormEvent $event): void {
    $this->addSelectAllToOptionButtons($event);
  }

  /**
   * Adds a 'select all' option to 'options_button' widgets.
   *
   * The 'options_buttons' field widget displays either a list of radio buttons
   * for single value fields, or checkboxes for multi-value fields. This method
   * alters the field widget settings form for multi-value fields (checkboxes)
   * only.
   *
   * @param \Drupal\field_event_dispatcher\Event\Field\FieldWidgetThirdPartySettingsFormEvent $event
   *   The event being fired.
   */
  public function addSelectAllToOptionButtons(FieldWidgetThirdPartySettingsFormEvent $event): void {
    $plugin = $event->getPlugin();
    $pluginId = $plugin->getPluginId();
    $isMultiple = $event->getFieldDefinition()->getFieldStorageDefinition()->isMultiple();
    if ($pluginId === 'options_buttons' && $isMultiple) {
      $elements = [];
      $elements['select_all'] = [
        '#type' => 'checkbox',
        '#title' => $this->t("Include 'select all' option"),
        '#default_value' => $plugin->getThirdPartySetting('va_gov_backend', 'select_all'),
        '#weight' => 100,
      ];
      $event->addElements('va_gov_backend', $elements);
    }
  }

  /**
   * Subscriber for hook_field_widget_single_element_WIDGET_TYPE_form_alter().
   *
   * @param \Drupal\field_event_dispatcher\Event\Field\WidgetSingleElementTypeFormAlterEvent $event
   *   The event being fired.
   */
  public function alterSingleElementOptionsButtonsForm(WidgetSingleElementTypeFormAlterEvent $event) {
    if (isset($event->getContext()['widget'])) {
      /** @var \Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsButtonsWidget $widget */
      $widget = $event->getContext()['widget'];
      $selectAllEnabled = $widget->getThirdPartySetting('va_gov_backend', 'select_all');
      if ($selectAllEnabled) {
        $element = &$event->getElement();
        $element['#prefix'] = '<span>testing</span>';
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      FieldHookEvents::FIELD_WIDGET_THIRD_PARTY_SETTINGS_FORM => 'alterFieldWidgetThirdPartySettingsForm',
      'hook_event_dispatcher.widget_single_element_options_buttons.alter' => 'alterSingleElementOptionsButtonsForm',
    ];
  }

}
