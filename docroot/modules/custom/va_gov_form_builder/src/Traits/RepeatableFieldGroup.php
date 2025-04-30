<?php

namespace Drupal\va_gov_form_builder\Traits;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Traits for repeatable fields.
 */
trait RepeatableFieldGroup {

  use StringTranslationTrait;

  /**
   * Creates repeatable fields dynamically using AJAX.
   */
  public function addRepeatableFieldGroup(array &$form, FormStateInterface $form_state, string $element_name, array $field_definitions, int $min = 1, int $max = 10, $startIndex = 1, $button_name = 'Add another') {
    // This should never happen, but you never know.
    if ($min > $max) {
      return;
    }
    // If an additional item would be over the limit, bail early.
    if ($startIndex > $max) {
      return;
    }

    // Get the number of ajax items in the form already.
    $num_items = $form_state->get($element_name . '_count');

    // If null, this is the first render.
    // We need to render at least $min items, and possibly
    // more if there are existing items.
    if ($num_items === NULL) {
      $num_items = max($min, $startIndex - 1);
      $form_state->set($element_name . '_count', $num_items);
    }

    // Create the container to hold our dynamic items.
    $form[$element_name . '_fieldset'] = [
      '#type' => 'container',
      '#attributes' => ['id' => $element_name . '-wrapper'],
    ];

    // Fields get their own values instead of copying the first group.
    $form[$element_name . '_fieldset'][$element_name]['#tree'] = TRUE;

    for ($i = 0; $i < $num_items - $startIndex + 1; $i++) {
      // Create a new fields for each item.
      foreach ($field_definitions as $field_key => $definition) {
        $form[$element_name . '_fieldset'][$element_name][$i][$field_key] = $definition;

        // Append index counter to the input label for a given set of fields.
        // - Custom key isn't set, so hideIndex === FALSE.
        // - Or if custom key exists and value isn't FALSE.
        $hideIndex = isset($form[$element_name . '_fieldset'][$element_name][$i][$field_key]['#displayIndexCount']);
        if ($hideIndex === FALSE || $form[$element_name . '_fieldset'][$element_name][$i][$field_key]['#displayIndexCount'] !== FALSE) {
          $label = $form[$element_name . '_fieldset'][$element_name][$i][$field_key]['#title'];
          $form[$element_name . '_fieldset'][$element_name][$i][$field_key]['#title'] = $label . ' ' . $i + $startIndex;
        }
      }
    }

    if ($num_items < $max) {
      $form[$element_name . '_fieldset']['actions'] = [
        '#type' => 'actions',
        '#attributes' => [
          'class' => ['form-builder-add-item-wrapper'],
        ],
      ];

      $form[$element_name . '_fieldset']['actions']['add_item'] = [
        '#type' => 'submit',
        '#name' => $element_name . '_add_item',
        '#value' => $this->t('@button', ['@button' => $button_name]),
        '#submit' => ['::addOne'],
        '#ajax' => [
          'callback' => '::addMoreCallback',
          'wrapper' => $element_name . '-wrapper',
        ],
        '#attributes' => [
          'class' => ['form-builder-add-item'],
        ],
        // Don't validate on ajax add, only on full page submission.
        '#limit_validation_errors' => [],
      ];
    }
  }

  /**
   * Callback for ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addMoreCallback(array &$form, FormStateInterface $form_state) {
    $clickedButton = $form_state->getTriggeringElement()['#name'];
    $elementWrapperName = str_replace('_add_item', '_fieldset', $clickedButton);
    return $form[$elementWrapperName];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $clickedButton = $form_state->getTriggeringElement()['#name'];
    $element_name = str_replace('_add_item', '_count', $clickedButton);

    // Increment counter.
    $num_field = $form_state->get($element_name);
    $new_count = $num_field + 1;
    $form_state->set($element_name, $new_count);

    $form_state->setRebuild();
  }

}
