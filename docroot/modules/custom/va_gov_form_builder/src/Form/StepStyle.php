<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderStepBase;

/**
 * Form step for the selecting a step style.
 */
class StepStyle extends FormBuilderStepBase {

  /**
   * The style of the step.
   *
   * 'repeating' or 'single'.
   *
   * @var string
   */
  protected $stepStyle;

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
    // any fields. But, since it's creating the
    // paragraph with the prevously entered
    // step label (field_title), we should validate
    // it here for good measure.
    return [
      'field_title',
    ];
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
      '#submit' => ['::handleEditStepLabelClick'],
      // No validation needed on this button.
      '#limit_validation_errors' => [],
    ];

    $form['actions']['add_repeating_set'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add a repeating set'),
      '#name' => 'add-repeating-set',
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
      '#name' => 'add-single-question',
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
   * Handler for the edit-step-label button.
   */
  public function handleEditStepLabelClick(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('va_gov_form_builder.step.add.step_label', [
      'nid' => $this->digitalForm->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function setStepParagraphFromFormState(FormStateInterface $form_state) {
    $stepLabel = $this->session->get('form_builder:add_step:step_label');

    if ($this->stepStyle === 'repeating') {
      $this->stepParagraph = $this->entityTypeManager->getStorage('paragraph')->create([
        'type' => 'digital_form_list_loop',
        'field_title' => $stepLabel,
      ]);
    }
    else {
      $this->stepParagraph = $this->entityTypeManager->getStorage('paragraph')->create([
        'type' => 'digital_form_custom_step',
        'field_title' => $stepLabel,
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $clickedButton = $form_state->getTriggeringElement()['#name'];

    if ($clickedButton === 'edit-step-label') {
      // Prevent validation for this specific button.
      return;
    }

    if ($clickedButton === 'add-repeating-set') {
      $this->stepStyle = 'repeating';
    }
    else {
      $this->stepStyle = 'single';
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->digitalForm->get('field_chapters')->appendItem($this->stepParagraph);
    $this->digitalForm->save();

    $form_state->setRedirect('va_gov_form_builder.layout', [
      'nid' => $this->digitalForm->id(),
    ]);
  }

}
