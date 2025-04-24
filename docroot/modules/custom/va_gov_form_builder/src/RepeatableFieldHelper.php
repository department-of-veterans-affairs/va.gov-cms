<?php
namespace Drupal\va_gov_form_builder;

use Drupal\Core\Form\FormStateInterface;

class RepeatableFieldHelper {

  // addRepeatableFieldGroup(
  //  &$form,
  //  $form_state,
  //  $element_name
  //  $field_definitions,
  //  $min,
  //  $max
  //);
  // $field_definitions would be the actual items you want repeated:
  // [
  //    'label' => ['#type' => 'textfield', '#title' => 'Label for radio item'],
  //    'description' => ['#type' => 'textfield', '#title' => 'Radio description for item'],
  //],
  public static function addRepeatableFieldGroup(array &$form, FormStateInterface $form_state, string $element_name, array $field_definitions, int $min = 1, int $max = 10) {
  // Track how many items we are showing.
  $field_count = $form_state->get($element_name . '_count');
  if ($field_count === NULL) {
    $field_count = $min;
    $form_state->set($element_name . '_count', $field_count);
  }

  $form[$element_name . '_wrapper'] = [
    '#type' => 'container',
    '#attributes' => ['id' => $element_name . '-wrapper'],
  ];

  for ($i = 0; $i < $field_count; $i++) {
    foreach ($field_definitions as $field_key => $definition) {
      $form[$element_name . '_wrapper'][$element_name][$i][$field_key] = $definition;
    }
  }

  // Add "Add another" button if we're under the max.
  if ($field_count < $max) {
    $form[$element_name . '_wrapper']['add_' . $element_name] = [
      '#type' => 'submit',
      '#value' => t('Add another'),
      '#submit' => [[self::class, 'addAnotherSubmit']],
      '#ajax' => [
        'callback' => [self::class, 'ajaxCallback'],
        'wrapper' => $element_name . '-wrapper',
      ],
      '#limit_validation_errors' => [],
      '#name' => 'add_' . $element_name,
    ];
  }
}

  public static function addAnotherSubmit(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement()['#name'];
    $prefix = 'add_';
    $element_name = str_starts_with($trigger, $prefix) ? substr($trigger, strlen($prefix)) : null;

    if ($element_name) {
      $current = $form_state->get($element_name . '_count');
      $form_state->set($element_name . '_count', $current + 1);
      $form_state->setRebuild(TRUE);
    }
  }

  public static function ajaxCallback(array &$form, FormStateInterface $form_state) {
    foreach ($form as $key => $element) {
      if (str_ends_with($key, '_wrapper')) {
        return $element;
      }
    }
    return [];
  }

}
