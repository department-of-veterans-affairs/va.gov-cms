<?php

namespace Drupal\va_gov_form_builder\Form\DigitalFormForm;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class FormNumber extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'digital_form_form__form_number';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $temp_store = \Drupal::service('tempstore.private')->get('va_gov_form_builder');
    $digital_form_in_progress = $temp_store->get('digital_form_in_progress');

    $form['#title'] = $this->t('Digital Form - Form Name');

    $form['form_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form Number'),
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $temp_store = \Drupal::service('tempstore.private')->get('va_gov_form_builder');
    $digital_form_in_progress = $temp_store->get('digital_form_in_progress');
    $digital_form_in_progress->set('field_va_form_number', $form_state->getValue('form_number'));
    $temp_store->set('digital_form_in_progress', $digital_form_in_progress);

    $form_state->setRedirect('va_gov_form_builder.digital_form_form.add_step.yes_no');
  }
}
