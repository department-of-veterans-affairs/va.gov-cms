<?php

namespace Drupal\expirable_content\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface as StorageDefinition;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'expirable_content' field type.
 *
 * @FieldType(
 *   id = "expirable_content",
 *   label = @Translation("Expirable Content"),
 *   description = @Translation("Defines an expirable content with expiration and warning dates."),
 *   default_widget = "datetime_default",
 *   default_formatter = "datetime_default"
 * )
 */
class ExpirableItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings(): array {
    return [
      'expiration_date' => '',
      'warning_date' => '',
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(StorageDefinition $storage) {
    $properties['expiration_date'] = DataDefinition::create('timestamp')
      ->setLabel(t('Expiration Date'));

    $properties['warning_date'] = DataDefinition::create('timestamp')
      ->setLabel(t('Warning Date'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(StorageDefinition $storage) {
    $schema = [
      'columns' => [
        'expiration_date' => [
          'type' => 'datetime',
          'not null' => FALSE,
        ],
        'warning_date' => [
          'type' => 'datetime',
          'not null' => FALSE,
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $expiration_date = $this->get('expiration_date')->getValue();
    $warning_date = $this->get('warning_date')->getValue();
    return empty($expiration_date) && empty($warning_date);
  }
}
