<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class StepAddChooseTypeForm.
 *
 * @package Drupal\form_builder\Form
 */
class StepAddChooseTypeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'step_add_choose_type_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Define the radio button options.
    $options = [
      'digital_form_name_and_date_of_bi' => $this->t('Name and Date of Birth'),
      'other' => $this->t('Other'),
    ];

    // Add the radio buttons element.
    $form['step_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Choose a step type'),
      '#options' => $options,
      '#default_value' => 'name_and_dob',
    ];

    // Add the submit button.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Add validation if needed.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Do nothing here; actual submission logic is handled in the controller.
  }
}
