<?php

namespace Drupal\va_gov_magichead\Plugin\Field\FieldType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_reference_hierarchy_revisions\Plugin\Field\FieldType\EntityReferenceHierarchyRevisionsItem;

/**
 * Defines the 'magichead' field type.
 *
 * @FieldType(
 *   id = "magichead",
 *   label = @Translation("Magichead"),
 *   category = @Translation("Reference revisions"),
 *   default_widget = "magichead_paragraphs_classic",
 *   default_formatter = "entity_reference_label",
 *   list_class = "\Drupal\va_gov_magichead\MagicheadFieldItemList",
 * )
 */
class MagicheadItem extends EntityReferenceHierarchyRevisionsItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'max_depth' => 3,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritDoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::fieldSettingsForm($form, $form_state);
    $element['max_depth'] = [
      '#type' => 'number',
      '#title' => $this->t('Max Depth'),
      '#description' => $this->t('The maximum depth of a magichead item.'),
      '#default_value' => $this->getSetting('max_depth'),
      '#min' => 0,
      '#weight' => -1,
    ];
    return $element;
  }

}
