<?php

namespace Drupal\va_gov_migrate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MigrateConfigForm.
 *
 * @package Drupal\va_gov_migrate\Form
 */
class MigrateConfigForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'va_gov_migrate_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['create_csv_files'] = [
      '#type' => 'checkbox',
      '#title' => 'Create CSV files.',
      '#default_value' => \Drupal::state()->get('va_gov_migrate.create_csv_files'),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::state()->set('va_gov_migrate.create_csv_files', $form_state->getValue('create_csv_files'));
  }

}
