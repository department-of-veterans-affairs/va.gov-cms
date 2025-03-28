<?php

namespace Drupal\va_gov_backend\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field_event_dispatcher\Event\Field\FieldWidgetThirdPartySettingsFormEvent;
use Drupal\field_event_dispatcher\Event\Field\WidgetSingleElementTypeFormAlterEvent;
use Drupal\field_event_dispatcher\FieldHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This subscriber adds a 'select all' option to the options buttons widget.
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
        '#default_value' => $plugin->getThirdPartySetting('va_gov_backend', 'Select_all'),
        '#weight' => 100,
      ];
      $elements['select_all_text'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Select all text'),
        '#default_value' => $plugin->getThirdPartySetting('va_gov_backend', 'select_all_text', $this->t('Select all')),
        '#weight' => 101,
        '#states' => [
          'visible' => [
            ':input[name*="' . $event->getFieldDefinition()->getName() . '"][name$="[third_party_settings][va_gov_backend][select_all]"]' => ['checked' => TRUE],
          ],
        ],
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
        $selectAllText = $widget->getThirdPartySetting('va_gov_backend', 'select_all_text', $this->t('Select all'));
        $element['select_all_wrapper'] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['select-all-options']],
          '#weight' => 0,
        ];
        $element['select_all_wrapper']['select_all'] = [
          '#type' => 'checkbox',
          '#title' => $selectAllText,
          '#attributes' => [
            'class' => [
              'select-all-options-checkbox',
            ],
            'data-select-all-options' => TRUE,
          ],
        ];
        $element['#attached']['library'][] = 'va_gov_backend/select_all_options';
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
