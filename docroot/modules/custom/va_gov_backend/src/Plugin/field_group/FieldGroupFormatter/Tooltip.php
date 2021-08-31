<?php

namespace Drupal\va_gov_backend\Plugin\field_group\FieldGroupFormatter;

use Drupal\field_group\Plugin\field_group\FieldGroupFormatter\HtmlElement;
use Drupal\Component\Utility\Html;

/**
 * Tooltip field group formatter.
 *
 * @FieldGroupFormatter(
 *   id = "tooltip",
 *   label = @Translation("Tooltip"),
 *   description = @Translation("Adds a tooltip field group"),
 *   supported_contexts = {
 *     "form",
 *     "view"
 *   }
 * )
 */
class Tooltip extends HtmlElement {

  /**
   * {@inheritdoc}
   */
  public function process(&$element, $processed_object) {
    parent::process($element, $processed_object);

    $element['#attached']['library'][] = 'va_gov_backend/field_group_tooltip';
    $element['#attached']['library'][] = 'va_gov_backend/tippy_popover_theme';

    $element['#attributes']['class'][] = 'tooltip-layout';

    if (!empty($this->getSetting('tooltip_description'))) {
      $element['tooltip_description'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#prefix' => '<button type="button" aria-label="tooltip" class="tooltip-toggle" role="presentation" title="' . $this->getSetting('tooltip_description') . '" value="' . $this->getSetting('tooltip_description') . '"></button>',
        '#attributes' => [
          'id' => [
            Html::getUniqueId('add-tooltip-description'),
          ],
        ],
        '#value' => $this->getSetting('tooltip_description'),
      ];
    }

    if (!empty($this->getSetting('description'))) {
      $element['description'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'aria-label' => 'tooltip',
          'role' => 'presentation',
          'class' => [
            'description',
          ],
        ],
        '#value' => $this->getSetting('description'),
      ];
    }

  }

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {
    parent::preRender($element, $rendering_object);
    $this->process($element, $rendering_object);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    $form = parent::settingsForm();

    $form['description'] = [
      '#title' => $this->t('Description'),
      '#type' => 'textarea',
      '#default_value' => $this->getSetting('description'),
      '#weight' => 3,
    ];

    $form['tooltip_description'] = [
      '#title' => $this->t('Tooltip Description'),
      '#type' => 'textarea',
      '#default_value' => $this->getSetting('tooltip_description'),
      '#weight' => 2,
    ];

    $form['attributes'] = [
      '#title' => $this->t('Attributes'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('attributes'),
      '#description' => $this->t('E.g. name="anchor"'),
      '#weight' => 5,
      '#access' => FALSE,
    ];

    $form['label_element'] = [
      '#title' => $this->t('Label element'),
      '#type' => 'textfield',
      '#default_value' => 'h3',
      '#weight' => 3,
      '#access' => FALSE,
    ];

    $form['element'] = [
      '#title' => $this->t('Element'),
      '#type' => 'textfield',
      '#default_value' => 'div',
      '#description' => $this->t('E.g. div, section, aside etc.'),
      '#weight' => 1,
      '#access' => FALSE,
    ];

    $form['effect'] = [
      '#access' => FALSE,
    ];

    $form['speed'] = [
      '#access' => FALSE,
    ];

    $form['label_element_classes'] = [
      '#access' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $summary = [];

    if ($this->getSetting('required_fields')) {
      $summary[] = $this->t('Mark as required');
    }

    if ($this->getSetting('show_label')) {
      $summary[] = $this->t('Show label');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultContextSettings($context) {
    $defaults = [
      'open' => FALSE,
      'required_fields' => $context == 'form',
    ] + parent::defaultSettings();

    if ($context === 'form') {
      $defaults['required_fields'] = 1;
    }

    return $defaults;
  }

}
