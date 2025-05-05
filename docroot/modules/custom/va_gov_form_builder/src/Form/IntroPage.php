<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderFormBase;

/**
 * Form step for entering a form's name and other basic info.
 *
 * Other basic info includes form number, OMB info, etc.
 */
class IntroPage extends FormBuilderFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__intro';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $digitalForm = NULL) {
    $this->allowEmptyDigitalForm = FALSE;
    $this->isCreate = FALSE;
    $form = parent::buildForm($form, $form_state, $digitalForm);

    $form['#theme'] = 'form__va_gov_form_builder__intro';

    $form['intro_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Intro paragraph'),
      '#required' => TRUE,
      '#default_value' => $this->getDigitalFormFieldValue('field_intro_text'),
    ];

    $form['what_to_know'] = [
      '#type' => 'textfield',
      '#title' => $this->t("What to know section"),
      '#required' => TRUE,
      '#default_value' => $this->getDigitalFormFieldValue('field_digital_form_what_to_know'),
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
  protected function setDigitalFormFromFormState(FormStateInterface $form_state) {
    $introText = $form_state->getValue('intro_text');
    $whatToKnow = $form_state->getValue('what_to_know');

    /*
     * This form is editing an existing Digital Form.
     *
     * We need to update only the fields from this form,
     * ensuring other fields are not changed.
     */
    $this->digitalForm->set('field_intro_text', $introText);
    $this->digitalForm->set('field_digital_form_what_to_know', $whatToKnow);
  }

  /**
   * {@inheritdoc}
   */
  protected function validateDigitalForm(array $form, FormStateInterface $form_state) {
    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
    $violations = $this->digitalForm->validate();

    if ($violations->count() > 0) {
      self::setFormErrors($form_state, $violations, [
        'field_intro_text' => $form['intro_text'],
        'field_digital_form_what_to_know' => $form['what_to_know'],
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $form_state->setRedirect('va_gov_form_builder.layout', [
      'nid' => $this->digitalForm->id(),
    ]);
  }

}
