<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderStepBase;

/**
 * Form step for the selecting a step style.
 */
class StepStyle extends FormBuilderStepBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__step_style';
  }

  /**
   * {@inheritdoc}
   */
  protected function getFields() {
    // This form does not directly interact with
    // any fields. Instead, it determines the type
    // of paragraph that will be created.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $digitalForm = NULL, $stepParagraph = NULL) {
    // On this form, the step paragraph should be allowed to be empty,
    // since it is always in "create" mode.
    $this->allowEmptyStepParagraph = TRUE;
    $this->isCreate = TRUE;

    $form = parent::buildForm($form, $form_state, $digitalForm, $stepParagraph);

    $form['#theme'] = 'form__va_gov_form_builder__step_style';

    $form['actions']['add_repeating_set'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add a repeating set'),
      '#attributes' => [
        'class' => [
          'button',
          'button--secondary',
          'form-submit',
        ],
      ],
      '#weight' => '10',
    ];

    $form['actions']['add_single_question'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add a single question'),
      '#attributes' => [
        'class' => [
          'button',
          'button--primary',
          'form-submit',
        ],
      ],
      '#weight' => '11',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function setStepParagraphFromFormState(FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
