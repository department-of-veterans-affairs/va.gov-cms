<?php

namespace Drupal\va_gov_form_builder\Traits;

use Drupal\Core\Form\FormStateInterface;

trait RepeatableFieldGroup {

  /**
   * Creates repeatable fields dynamically using AJAX.
   */
  public function addRepeatableFieldGroup(array &$form, FormStateInterface $form_state, string $element_name, array $field_definitions, int $min = 1, int $max = 10) {
    // Get the number of items in the form already.
    $num_items = $form_state->get($element_name . '_count');
    // We have to ensure that there is at least one item field.
    if ($num_items === NULL) {
      $form_state->set($element_name . '_count', 1);
      $num_items = $form_state->get($element_name . '_count');
    }

    // Create the container to hold our dynamic items.
    $form[$element_name . '_fieldset'] = [
      '#type' => 'container',
      '#attributes' => ['id' => $element_name . '-wrapper'],
    ];

    for ($i = 0; $i < $num_items; $i++) {
      // Create a new fields for each item.
      foreach ($field_definitions as $field_key => $definition) {
        $form[$element_name . '_fieldset'][$element_name][$i][$field_key] = $definition;
      }
    }

    if ($num_items < $max) {
      $form[$element_name . '_fieldset']['actions'] = [
        '#type' => 'actions',
      ];

      $form[$element_name . '_fieldset']['actions']['add_item'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add another'),
        '#submit' => ['::addOne'],
        // '#submit' => ['::addOne']
        '#ajax' => [
          'callback' => '::addMoreCallback',
          // 'callback' => '::addMoreCallback',
          'wrapper' => $element_name . '-wrapper',
        ],
      ];
    }
  }

  /**
   * Callback for ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addMoreCallback(array &$form, FormStateInterface $form_state) {
    // @todo Make this field dynamic somehow.
    return $form['dynamic_radio_fieldset'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    // @todo Make this field dynamic somehow.
    $num_field = $form_state->get('dynamic_radio_count');
    $new_count = $num_field + 1;
    $form_state->set('dynamic_radio_count', $new_count);
    $form_state->setRebuild();
    $form_state->disableCache();
  }

}
