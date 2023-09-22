<?php

namespace Drupal\va_gov_magichead\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Plugin\Field\FieldWidget\ParagraphsWidget;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of the 'magichead paragraphs' widget.
 *
 * @FieldWidget(
 *   id = "magichead_paragraphs_stable",
 *   label = @Translation("Magichead Paragraphs (stable)"),
 *   description = @Translation("The stable magichead paragraphs inline form widget."),
 *   field_types = {
 *     "magichead"
 *   }
 * )
 */
class MagicHeadParagraphsWidget extends ParagraphsWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['depth'] = [
      '#type' => 'textfield',
      '#size' => 3,
      '#title' => $this->t('Depth for row @number', ['@number' => $delta + 1]),
      '#title_display' => 'invisible',
      '#default_value' => !empty($items[$delta]->depth) ? $items[$delta]->depth : 0,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * @see va_gov_magichead_preprocess_field_multiple_value_form()
   */
  public function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);

    // Attach our libraries.
    $elements['#attached']['library'][] = 'va_gov_magichead/magichead_tree_lines';
    $elements['#attached']['library'][] = 'va_gov_magichead/magichead';

    // Set an arbitrary value for easy targeting in preprocess hook.
    $elements['#magichead'] = TRUE;

    // Set the max depth on the element. This allows for direct access of max
    // depth in preprocess hooks.
    $elements['#magichead_max_depth'] = $items->getFieldDefinition()->getSetting('max_depth');
    $elements['#entity_reference_hierarchy_paragraphs'] = TRUE;

    return $elements;
  }

  /**
   * {@inheritDoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $error, array $form, FormStateInterface $form_state) {
    $error_element = NestedArray::getValue($element, $error->arrayPropertyPath);
    return is_array($error_element) ? $error_element : $element;
  }

}
