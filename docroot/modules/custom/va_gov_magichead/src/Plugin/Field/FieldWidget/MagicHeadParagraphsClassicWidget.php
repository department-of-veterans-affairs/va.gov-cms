<?php

namespace Drupal\va_gov_magichead\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_reference_hierarchy_paragraphs\Plugin\Field\FieldWidget\InlineParagraphsHierarchyWidget;

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
   */
  public static function defaultSettings() {
    return [
      'max_depth' => 3,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['max_depth'] = [
      '#type' => 'number',
      '#title' => $this->t('Max Depth'),
      '#description' => $this->t('The maximum depth of a magichead item.'),
      '#default_value' => $this->getSetting('max_depth'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @see va_gov_magichead_preprocess_field_multiple_value_form()
   */
  public function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);

    // Attach our library.
    $elements['#attached']['library'][] = 'va_gov_magichead/magichead_tree_lines';

    // Set an arbitrary value for easy targeting in preprocess hook.
    $elements['#magichead'] = TRUE;

    // Set the max depth on the element. This allows for direct access of max
    // depth in preprocess hooks.
    $elements['#magichead_max_depth'] = $this->getSetting('max_depth');

    return $elements;
  }

}
