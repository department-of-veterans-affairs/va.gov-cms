<?php

namespace Drupal\va_gov_magichead\Plugin\Field\FieldWidget;

use Drupal\entity_reference_hierarchy_paragraphs\Plugin\Field\FieldWidget\InlineParagraphsHierarchyWidget;

/**
 * Plugin implementation of 'entity_reference_hierarchy_autocomplete' widget.
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
  public function formElement(FieldItemListInterface $items, $delta, array &$element, array &$form,
    FormStateInterface $form_state) {
    $element['magichead_paragraphs_classic']['#attached']['library'][] = 'va_gov_magichead/magichead_tree_lines';
  }

}
