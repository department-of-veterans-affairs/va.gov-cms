<?php

namespace Drupal\disable_node_menu_item\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;

/**
 * Settings form for the Disable Node Menu Item module.
 */
class DisableNodeMenuItemSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'disable_node_menu_item_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['disable_node_menu_item.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('disable_node_menu_item.settings');

    // Only provide option for selected node types.
    $enabled_types = $config->get('node_types');
    $checkboxes = [];
    foreach (NodeType::loadMultiple() as $type_id => $type) {
      $checkboxes[$type_id] = Html::escape($type->label());
      $default[] = $type_id;
    }

    $form['node_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content Type Selection'),
      '#default_value' => $enabled_types,
      '#options' => $checkboxes,
      '#description' => $this->t('This setting determines what content types the disable node menu item functionality will be included on.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('disable_node_menu_item.settings')
      ->set('node_types', array_keys(array_filter($form_state->getValue('node_types'))))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
