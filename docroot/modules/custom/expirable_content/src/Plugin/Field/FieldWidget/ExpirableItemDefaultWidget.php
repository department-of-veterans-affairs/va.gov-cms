<?php

namespace Drupal\expirable_content\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Color;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'expirable_item_default_widget' widget.
 *
 * @FieldWidget(
 *   id = "expirable_item_default_widget",
 *   module = "expirable_content",
 *   label = @Translation("Expiration (default)"),
 *   field_types = {
 *     "expirable_item"
 *   }
 * )
 */
class ExpirableItemDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $expire = $items[$delta]->expire;
    $warn = $items[$delta]->warn;
    $element['expire_warn_container'] = [
      '#title' => $this->t('Expiration information'),
      '#type' => 'fieldset',
      '#description' => $this->t('This data is set programmatically.')
    ];
    $element['expire_warn_container']['expire'] = [
      '#title' => $this->t('Expire date'),
      '#type' => 'textfield',
      '#disabled' => TRUE,
      '#default_value' => $expire ? DrupalDateTime::createFromTimestamp($expire)->format('r') : '',
    ];
    $element['expire_warn_container']['warn'] = [
      '#title' => $this->t('Warn date'),
      '#type' => 'textfield',
      '#disabled' => TRUE,
      '#default_value' => $warn ? DrupalDateTime::createFromTimestamp($warn)->format('r') : '',
    ];
    return $element;
  }

}
