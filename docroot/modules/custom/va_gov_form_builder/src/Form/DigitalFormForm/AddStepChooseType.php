<?php

namespace Drupal\va_gov_form_builder\Form\DigitalFormForm;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AddStepChooseType extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'digital_form_form__add_step_choose_type';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    $temp_store = \Drupal::service('tempstore.private')->get('va_gov_form_builder');
    $digital_form_in_progress = $temp_store->get('digital_form_in_progress');

    $form['#title'] = $this->t('Digital Form - Add Step - Choose Type');

    $form['nid'] = [
      '#type' => 'hidden',
      '#value' => $node->id(),
    ];

    $form['step_type'] = [
      '#title' => $this->t('What type of step would you like to add?'),
      '#type' => 'radios',
      '#options' => [
        'digital_form_name_and_date_of_bi' => $this->t('Name and DOB'),
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
    $nid = $form_state->getValue('nid');
    $step_type = $form_state->getValue('step_type');

    $form_state->setRedirect('va_gov_form_builder.digital_form.edit.add_step', [
      'nid' => $nid,
      'step_type' => $step_type
    ]);
  }
}
