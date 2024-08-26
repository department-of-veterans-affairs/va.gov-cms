<?php

namespace Drupal\va_gov_form_builder\Form\DigitalFormForm;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AddStepYesNo extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'digital_form_form__add_step';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $temp_store = \Drupal::service('tempstore.private')->get('va_gov_form_builder');
    $digital_form_in_progress = $temp_store->get('digital_form_in_progress');

    $form['#title'] = $this->t('Digital Form - Add Step');

    $form['add_more_steps'] = [
      '#title' => $this->t('Do you have any steps to add?'),
      '#type' => 'radios',
      '#options' => [
        'yes' => $this->t('Yes'),
        'no' => $this->t('No'),
      ],
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $add_more_steps = $form_state->getValue('add_more_steps');

    if ($add_more_steps === 'yes') {
      $form_state->setRedirect('va_gov_form_builder.digital_form_form.add_step.choose_type');
    } else {
      $form_state->setRedirect('va_gov_form_builder.digital_form_form.review_and_submit');
    }

  }
}
