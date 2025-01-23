<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderBase;

/**
 * Form step for entering a form's name and other basic info.
 *
 * Other basic info includes form number, OMB info, etc.
 */
class FormInfo extends FormBuilderBase {

  /**
   * Flag indicating if the form mode is "create".
   *
   * Form mode is "create", and this value is TRUE,
   * if no node id is passed in representing an existing node.
   *
   * Form mode is "edit" otherwise, and this value is FALSE.
   *
   * @var bool
   */
  protected $isCreate;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__form_info';
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
  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    // On this form, the Digital Form node should be allowed to be empty,
    // to accommodate the case where it is in "create" mode.
    $this->allowEmptyDigitalFormNode = TRUE;
    $form = parent::buildForm($form, $form_state, $node);

    if (empty($node)) {
      // If no node is passed in, this is "create" mode.
      $this->isCreate = TRUE;
    }
    else {
      // If a node is passed in, this is "edit" mode.
      $this->isCreate = FALSE;
    }

    $form['#theme'] = 'form__va_gov_form_builder__form_info';

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form Name'),
      '#required' => TRUE,
      '#default_value' => $this->getDigitalFormNodeFieldValue('title'),
    ];

    $form['field_va_form_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form Number'),
      '#required' => TRUE,
      '#default_value' => $this->getDigitalFormNodeFieldValue('field_va_form_number'),
    ];

    $form['field_omb_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OMB number'),
      '#description' => $this->t('Insert the OMB number (format: xxxx-xxxx)'),
      '#required' => TRUE,
      '#default_value' => $this->getDigitalFormNodeFieldValue('field_omb_number'),
    ];

    $form['field_respondent_burden'] = [
      '#type' => 'number',
      '#title' => $this->t('Respondent burden'),
      '#description' => $this->t('Number of minutes as indicated on the form'),
      '#required' => TRUE,
      '#default_value' => $this->getDigitalFormNodeFieldValue('field_respondent_burden'),
    ];

    $form['field_expiration_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Expiration date'),
      '#description' => $this->t('Form expiration date as indicated on the form'),
      '#required' => TRUE,
      '#default_value' => $this->getDigitalFormNodeFieldValue('field_expiration_date'),
    ];

    $form['actions']['save_and_continue'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function setDigitalFormNodeFromFormState(array &$form, FormStateInterface $form_state) {
    $title = $form_state->getValue('title');
    $vaFormNumber = $form_state->getValue('field_va_form_number');
    $ombNumber = $form_state->getValue('field_omb_number');
    $respondentBurden = $form_state->getValue('field_respondent_burden');
    $expirationDate = $form_state->getValue('field_expiration_date');

    if ($this->isCreate) {
      /*
       * This form is creating a new node.
       *
       * We can simply create the new node with the fields from this form.
       */
      $this->digitalFormNode = $this->entityTypeManager->getStorage('node')->create([
        'type' => 'digital_form',
        'title' => $title,
        'field_va_form_number' => $vaFormNumber,
        'field_omb_number' => $ombNumber,
        'field_respondent_burden' => $respondentBurden,
        'field_expiration_date' => $expirationDate,
      ]);
    }
    else {
      /*
       * This form is editing an existing node.
       *
       * We need to update only the fields from this form,
       * ensuring other fields are not changed.
       */
      $this->digitalFormNode->set('title', $title);
      $this->digitalFormNode->set('field_va_form_number', $vaFormNumber);
      $this->digitalFormNode->set('field_omb_number', $ombNumber);
      $this->digitalFormNode->set('field_respondent_burden', $respondentBurden);
      $this->digitalFormNode->set('field_expiration_date', $expirationDate);
    }
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
