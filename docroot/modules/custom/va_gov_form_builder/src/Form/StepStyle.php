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

    $form['edit_step_label'] = [
      '#type' => 'submit',
      '#value' => $this->t('Edit step label'),
      '#name' => 'edit-step-label',
      '#attributes' => [
        'class' => [
          'button',
          'button--secondary',
        ],
      ],
      '#submit' => ['::handleEditStepLabel'],
      // No validation needed on this button.
      '#limit_validation_errors' => [],
    ];

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
      '#weight' => '20',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function setStepParagraphFromFormState(FormStateInterface $form_state) {

  }

  /**
   * Handler for the edit-step-label button.
   */
  public function handleEditStepLabel(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('va_gov_form_builder.step.add.step_label', [
      'nid' => $this->digitalForm->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#name'] === 'edit-step-label') {
      // Prevent validation for this specific button.
      return;
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
