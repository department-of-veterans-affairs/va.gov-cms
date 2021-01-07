<?php

namespace Drupal\entity_field_fetch\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Provides a field type of EntityFieldFetch.
 *
 * @FieldType(
 *   id = "entity_field_fetch",
 *   label = @Translation("Entity Field Fetch field"),
 *   description = @Translation("This field TYPE, not item, stores the target information needed to fetch field data from a single source."),
 *   default_formatter = "entity_field_fetch",
 *   default_widget = "entity_field_fetch_widget",
 * )
 */
class EntityFieldFetchItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      // Columns contains the values that the field will store.
      'columns' => [
        // This field should store nothing on the node.
        'target_type' => [
          'type' => 'varchar',
          'length' => 36,
        ],
        'target_uuid' => [
          'type' => 'varchar',
          'length' => 36,
        ],
        'target_field' => [
          'type'   => 'varchar',
          'length' => 32,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = [];
    $properties['target_type'] = DataDefinition::create('string')
      ->setLabel(t('Entity type'))
      ->setDescription(t('Node or Term'));
    $properties['target_uuid'] = DataDefinition::create('string')
      ->setLabel(t('Target entity UUID'))
      ->setDescription(t('The uuid of the target entity.'));
    $properties['target_field'] = DataDefinition::create('string')
      ->setLabel(t('Target field'))
      ->setDescription(t('The machine name of the field to pull from.'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    // @todo Workout what should be treated as empty... its complicated.
    // $value = $this->get('value')->getValue();
    $value = "beeee";
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      // Declare the settings with defaults.
      'target_entity_type' => '',
      'target_entity_id' => '',
      'field_to_fetch' => '',
      'target_paragraph_uuid' => '',
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $element['target_entity_type'] = [
      '#title' => $this->t('Target entity type'),
      '#type' => 'select',
      '#options' => [
        'node' => $this->t('Node'),
        'term' => $this->t('Term'),
      ],
      '#required' => FALSE,
      '#default_value' => $this->getSetting('target_entity_type'),
    ];

    $element['target_entity_id'] = [
      '#title' => $this->t('Target entity id @examples.', ['@examples' => '(nid or tid)']),
      '#type' => 'number',
      '#size' => 36,
      '#min' => 1,
      '#required' => TRUE,
      '#default_value' => $this->getSetting('target_entity_id'),
    ];

    $element['field_to_fetch'] = [
      '#title' => $this->t('Machine name of field to fetch.'),
      '#type' => 'textfield',
      '#size' => 32,
      '#maxlength' => 32,
      '#required' => TRUE,
      '#default_value' => $this->getSetting('field_to_fetch'),
    ];

    $element['target_paragraph_uuid'] = [
      '#title' => $this->t('Paragraph UUID to fetch (optional)'),
      '#type' => 'textfield',
      '#size' => 36,
      '#maxlength' => 36,
      '#required' => FALSE,
      '#default_value' => $this->getSetting('target_paragraph_uuid'),
    ];

    return $element;
  }

}
