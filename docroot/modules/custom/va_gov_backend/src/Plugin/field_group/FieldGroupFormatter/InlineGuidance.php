<?php

namespace Drupal\va_gov_backend\Plugin\field_group\FieldGroupFormatter;

use Drupal\field_group\Plugin\field_group\FieldGroupFormatter\HtmlElement;

/**
 * Inline Guidance field group formatter.
 *
 * @FieldGroupFormatter(
 *   id = "inline_guidance",
 *   label = @Translation("Inline Guidance"),
 *   description = @Translation("Adds inline guidance to a field group."),
 *   supported_contexts = {
 *     "form",
 *     "view"
 *   }
 * )
 */
class InlineGuidance extends HtmlElement {

  /**
   * {@inheritdoc}
   */
  public function process(&$element, $processed_object) {
    parent::process($element, $processed_object);

    $element['#attributes']->addClass('inline_guidance');

    $element['inline_guidance_help_text'] = [
      '#value' => $this->getSetting('inline_guidance_help_text'),
    ];
    $element['inline_guidance'] = [
      '#value' => $this->getSetting('inline_guidance'),
    ];
    $element['inline_guidance_classes'] = [
      '#value' => $this->getSetting('inline_guidance_classes'),
    ];
    $element['trigger_button_title'] = [
      '#value' => $this->getSetting('trigger_button_title'),
    ];
    $element['trigger_button_classes'] = [
      '#value' => $this->getSetting('trigger_button_classes'),
    ];
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

    $form['inline_guidance_help_text'] = [
      '#title' => $this->t('Inline Guidance Help Text'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('inline_guidance_help_text'),
      '#description' => $this->t('Help text that goes before the trigger button.'),
      '#weight' => 4,
    ];

    $form['trigger_button_title'] = [
      '#title' => $this->t('Trigger Button Title'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('trigger_button_title'),
      '#description' => $this->t('Normally the field title.'),
      '#weight' => 5,
    ];

    $form['trigger_button_classes'] = [
      '#title' => $this->t('Trigger Button Classes'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('trigger_button_classes'),
      '#description' => $this->t('Additional classes to allow for additional theming of the trigger button.'),
      '#weight' => 6,
    ];

    $form['inline_guidance'] = [
      '#title' => $this->t('Inline Guidance'),
      '#type' => 'text_format',
      '#format' => $this->getSetting('inline_guidance')['format'] ?? 'rich_text',
      '#default_value' => $this->getSetting('inline_guidance')['value'] ?? '',
      '#description' => $this->t('The actual guidance text to be displayed.'),
      '#weight' => 7,
    ];

    $form['inline_guidance_classes'] = [
      '#title' => $this->t('Classes for inline guidance text box.'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('inline_guidance_classes'),
      '#description' => $this->t('Additional classes to allow for additional theming of the text box.'),
      '#weight' => 8,
    ];

    $form['element'] = [
      '#title' => $this->t('Element'),
      '#type' => 'textfield',
      '#default_value' => 'div',
      '#description' => $this->t('E.g. div, section, aside etc.'),
      '#weight' => 1,
      '#access' => FALSE,
    ];

    $form['attributes'] = [
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

    $form['label_element'] = [
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
