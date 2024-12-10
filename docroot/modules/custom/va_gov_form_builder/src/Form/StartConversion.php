<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderNodeBase;

/**
 * Form step for starting a new form conversion.
 */
class StartConversion extends FormBuilderNodeBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__start_conversion';
  }

  /**
   * {@inheritdoc}
   */
  protected function getFields() {
    return [
      'title',
      'field_va_form_number',
      'field_omb_number',
      'field_respondent_burden',
      'field_expiration_date',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['start_new_conversion_header'] = [
      '#type' => 'html_tag',
      '#tag' => 'h2',
      '#children' => $this->t('Start a new conversion'),
    ];

    $form['help_text'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#children' => $this->t('Begin your transformation of this form by including this information to start.
        Refer to your existing form to copy this information over.'),
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form Name'),
      '#description' => $this->t('Insert the form name'),
      '#required' => TRUE,
    ];

    $form['field_va_form_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form Number'),
      '#description' => $this->t('Insert the form number'),
      '#required' => TRUE,
    ];

    $form['omb_header'] = [
      '#type' => 'item',
      '#title' => $this->t('OMB information'),
      '#description' => $this->t('Refer to the form'),
    ];

    $form['field_omb_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OMB number'),
      '#description' => $this->t('Insert the OMB number (format: xxxx-xxxx)'),
      '#required' => TRUE,
    ];

    $form['field_respondent_burden'] = [
      '#type' => 'number',
      '#title' => $this->t('Respondent burden'),
      '#description' => $this->t('Number of minutes as indicated on the form'),
      '#required' => TRUE,
    ];

    $form['field_expiration_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Expiration date'),
      '#description' => $this->t('Form expiration date as indicated on the form'),
      '#required' => TRUE,
    ];

    $form['actions']['continue'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#attributes' => [
        'class' => [
          'button',
          'button--primary',
          'js-form-submit',
          'form-submit',
        ],
      ],
      '#weight' => '10',
    ];

    $form['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#weight' => '20',
      '#submit' => ['::backButtonSubmitHandler'],
      '#limit_validation_errors' => [],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function setDigitalFormNodeFromFormState(array &$form, FormStateInterface $form_state) {
    $this->digitalFormNode = $this->entityTypeManager->getStorage('node')->create([
      'type' => 'digital_form',
      'title' => $form_state->getValue('title'),
      'field_va_form_number' => $form_state->getValue('field_va_form_number'),
      'field_omb_number' => $form_state->getValue('field_omb_number'),
      'field_respondent_burden' => $form_state->getValue('field_respondent_burden'),
      'field_expiration_date' => $form_state->getValue('field_expiration_date'),
    ]);
  }

  /**
   * Submit handler for the 'Back' button.
   */
  public function backButtonSubmitHandler(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('va_gov_form_builder.intro');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $form_state->setRedirect('va_gov_form_builder.name_and_dob', [
      'nid' => $this->digitalFormNode->id(),
    ]);
  }

}
