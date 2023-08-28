<?php

namespace Drupal\va_gov_magichead\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_reference_hierarchy_paragraphs\Plugin\Field\FieldWidget\InlineParagraphsHierarchyWidget;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of 'magichead_paragraphs_classic' widget.
 *
 * @FieldWidget(
 *   id = "magichead_paragraphs_classic",
 *   label = @Translation("Magichead Paragraphs"),
 *   description = @Translation("magichead paragraphs."),
 *   field_types = {
 *     "magichead"
 *   }
 * )
 */
class MagicHeadParagraphsClassicWidget extends InlineParagraphsHierarchyWidget {

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
