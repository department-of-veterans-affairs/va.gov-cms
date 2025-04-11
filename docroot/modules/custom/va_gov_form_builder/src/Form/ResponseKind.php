<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderBase;

/**
 * Form step for the defining/editing a step label.
 */
class ResponseKind extends FormBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__response_kind';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $digitalForm = NULL, $stepParagraph = NULL) {
    $form = parent::buildForm($form, $form_state, $digitalForm, $stepParagraph);

    $form['#theme'] = 'form__va_gov_form_builder__response_kind';

    $form['response_kind'] = [
      '#type' => 'radio',
      '#title' => $this->t('What kind of response will the submitter provide?'),
      '#options' => [
        'pizza' => $this->t('Pizza'),
        'hamburger' => $this->t('Hamburger'),
        'sandwich' => $this->t('Sandwich'),
      ],
      '#required' => TRUE,
    ];

    $form['actions']['save_and_continue'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
      '#attributes' => [
        'class' => [
          'button',
          'button--primary',
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    xdebug_var_dump($form_state->getValues());
    exit;
  }

}
