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
    $settings = parent::defaultSettings();
    return $settings + [
      'max_depth' => 3,
    ];
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
   * {@inheritDoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['#attached']['library'][] = 'va_gov_magichead/magichead_tree_lines';
    $element['#attached']['library'][] = 'va_gov_magichead/magichead';
    $name = str_replace("_", "-", "{$this->fieldDefinition->getName()}-values");
    $settings = [
      'tablefield_target' => $name,
      'field_name' => $this->fieldDefinition->getName(),
      'max_depth' => $this->getSetting('max_depth'),
    ];
    $element['#attached']['drupalSettings']['magichead'][] = $settings;
    return $element;
  }

}
