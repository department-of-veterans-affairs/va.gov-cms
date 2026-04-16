<?php

namespace Drupal\va_gov_form_builder\Element;

use Drupal\Core\Render\Element\Radios;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a custom expanded-radio input.
 *
 * @FormElement("va_gov_form_builder__expanded_radios")
 */
class ExpandedRadios extends Radios {

  /**
   * Configures each individual radio item.
   */
  public static function processRadios(&$element, FormStateInterface $form_state, &$complete_form) {
    $element = parent::processRadios($element, $form_state, $complete_form);
    foreach ($element['#options'] as $key => $choice) {
      $element[$key]['#type'] = 'va_gov_form_builder__expanded_radio';
      $element[$key]['#expanded_content'] = $element['#options_expanded_content'][$key] ?? [];
    }

    return $element;
  }

}
