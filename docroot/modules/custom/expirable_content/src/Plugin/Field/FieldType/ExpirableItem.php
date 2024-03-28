<?php

namespace Drupal\expirable_content\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'expirable_item' field type.
 *
 * @FieldType(
 *   id = "expirable_item",
 *   label = @Translation("Expiration"),
 *   description = @Translation("Stores expiration information for an entity including expiration date and warning date."),
 *   default_widget = "expirable_item_default_widget",
 *   default_formatter = "expirable_item_default_formatter",
 * )
 */
class ExpirableItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings(): array {
    return [
      'expire' => '',
      'warn' => '',
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'expire';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition): array {
    $properties['expire'] = DataDefinition::create('timestamp')
      ->setLabel(t('Expiration Date'));
    $properties['warn'] = DataDefinition::create('timestamp')
      ->setLabel(t('Warning Date'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition): array {
    return [
      'columns' => [
        'expire' => [
          'type' => 'int',
        ],
        'warn' => [
          'type' => 'int',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $expiration_date = $this->get('expire')->getValue();
    $warning_date = $this->get('warn')->getValue();
    return empty($expiration_date) && empty($warning_date);
  }

}
