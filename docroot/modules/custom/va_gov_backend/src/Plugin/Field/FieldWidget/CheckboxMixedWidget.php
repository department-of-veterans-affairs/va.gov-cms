<?php

namespace Drupal\va_gov_backend\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\options\Plugin\Field\FieldWidget\OptionsButtonsWidget;

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
  public static function defaultSettings() {
    return [
      'select_all' => FALSE,
      'select_all_text' => t('Select all'),
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['select_all'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Include 'select all' option"),
      '#default_value' => $this->getSetting('select_all'),
    ];

    $element['select_all_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Select all text'),
      '#default_value' => $this->getSetting('select_all_text'),
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][select_all]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($this->getSetting('select_all')) {
      $summary[] = $this->t('Select all option enabled with text: @text', ['@text' => $this->getSetting('select_all_text')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $parentElement = parent::formElement($items, $delta, $element, $form, $form_state);

    if ($this->fieldDefinition->getFieldStorageDefinition()->isMultiple() && $this->getSetting('select_all')) {
      $element['#attached']['library'][] = 'va_gov_backend/checkbox_mixed';

      $element['checkbox_mixed'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['checkbox-mixed']],
        '#weight' => 0,
      ];

      $element['checkbox_mixed']['checkbox_mixed_checkbox'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->getSetting('select_all_text'),
        '#attributes' => [
          'class' => [
            'checkbox-mixed-checkbox',
          ],
          'role' => 'checkbox',
          'aria-checked' => 'mixed',
          'tabindex' => '0',
        ],
      ];
      $element['checkbox_mixed']['children'] = $parentElement;
    }

    return $element;
  }

}
