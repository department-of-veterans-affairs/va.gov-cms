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
   * {@inheritDoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['#attached']['library'][] = 'va_gov_magichead/magichead_tree_lines';
    return $element;
  }

}
