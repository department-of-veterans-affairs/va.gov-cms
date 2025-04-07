<?php

namespace Drupal\va_gov_backend\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsButtonsWidget;

/**
 * Plugin implementation of the 'checkbox_mixed' widget.
 *
 * @FieldWidget(
 *   id = "checkbox_mixed",
 *   label = @Translation("Check boxes/radio buttons with select all"),
 *   field_types = {
 *     "entity_reference",
 *     "list_integer",
 *     "list_float",
 *     "list_string",
 *     "list_text",
 *     "boolean",
 *   },
 *   multiple_values = TRUE
 * )
 */
class CheckboxMixedWidget extends OptionsButtonsWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    return [
      'select_all_text' => t('Select all'),
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $element = parent::settingsForm($form, $form_state);
    $element['select_all_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Select all text'),
      '#default_value' => $this->getSetting('select_all_text'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Select all text: @text', ['@text' => $this->getSetting('select_all_text')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['#attached']['library'][] = 'va_gov_backend/checkbox_mixed';
    $element['#theme_wrappers'] = [
      'container' => [
        '#attributes' => ['class' => ['checkbox-mixed-wrapper']],
        '#title_display' => 'invisible',
      ],
    ];
    $element['checkbox_mixed'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['checkbox-mixed']],
      '#weight' => 0,
    ];

    $element['checkbox_mixed']['checkbox_mixed_checkbox'] = [
      '#type' => 'checkbox',
      '#title' => $this->getSetting('select_all_text'),
      '#attributes' => [
        'class' => [
          'checkbox-mixed-checkbox',
        ],
        'role' => 'checkbox',
        'aria-checked' => 'mixed',
        'tabindex' => '0',
      ],
      // Set a value to satisfy the checkbox element.
      '#value' => FALSE,
      // Prevent this element from being considered a field value.
      '#input' => FALSE,
    ];

    return $element;
  }

}
