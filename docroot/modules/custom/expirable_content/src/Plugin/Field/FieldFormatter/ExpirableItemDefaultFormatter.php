<?php

namespace Drupal\expirable_content\Plugin\Field\FieldFormatter;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'field_example_simple_text' formatter.
 *
 * @FieldFormatter(
 *   id = "expirable_item_default_formatter",
 *   module = "expirable_content",
 *   label = @Translation("Expirable content simple text-based formatter"),
 *   field_types = {
 *     "expiration"
 *   }
 * )
 */
class ExpirableItemDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#type' => 'markup',
        '#markup' => $this->t('Expiration: @expiration, Warning: @warning', [
          '@expiration' => DrupalDateTime::createFromTimestamp($item->expiration_date)->format('r'),
          '@warning' => DrupalDateTime::createFromTimestamp($item->warning_date)->format('r'),
        ]),
      ];
    }
    return $elements;
  }

}
