<?php

namespace Drupal\va_gov_migrate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * A form with a button to delete main menu items.
 *
 * @package Drupal\va_gov_migrate\Form
 */
class VaMenuTruncateForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'va_menu_truncate_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove All Menu Links From Main Menu'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::service('plugin.manager.menu.link')->deleteLinksInMenu('main');
    return TRUE;
  }

}
