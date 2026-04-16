<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderStepBase;

/**
 * Form step for the defining/editing a step label.
 */
class StepLabel extends FormBuilderStepBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__step_label';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $digitalForm = NULL, $stepParagraph = NULL) {
    // On this form, the step paragraph should be allowed to be empty,
    // to accommodate the case where it is in "create" mode.
    $this->allowEmptyStepParagraph = TRUE;
    $form = parent::buildForm($form, $form_state, $digitalForm, $stepParagraph);

    if (empty($stepParagraph)) {
      // If no step paragraph is passed in, this is "create" mode.
      $this->isCreate = TRUE;
      $labelDefaultValue = $this->session->get(self::SESSION_KEY);
    }
    else {
      // If a step paragraph is passed in, this is "edit" mode.
      $this->isCreate = FALSE;
      $labelDefaultValue = $this->getStepParagraphFieldValue('field_title');
    }

    $form['#theme'] = 'form__va_gov_form_builder__step_label';

    $form['step_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Step label'),
      '#required' => TRUE,
      '#default_value' => $labelDefaultValue,
    ];

    $form['preview'] = [
      '#type' => 'html_tag',
      '#tag' => 'img',
      '#attributes' => [
        'src' => self::IMAGE_DIR . 'step-label.png',
        'alt' => $this->t('Step-label preview'),
      ],
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
  protected function setStepParagraphFromFormState(FormStateInterface $form_state) {
    $stepLabel = $form_state->getValue('step_label');

    if ($this->isCreate) {
      // We don't know at this point what type of paragraph we need,
      // but we're not actually going to persist this paragraph anyway.
      // We just need a paragraph with a `field_title` so we can validate
      // that field.
      $this->stepParagraph = $this->entityTypeManager->getStorage('paragraph')->create([
        'type' => 'digital_form_custom_step',
        'field_title' => $stepLabel,
      ]);
    }
    else {
      $this->stepParagraph->set('field_title', $stepLabel);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function validateStepParagraph(array $form, FormStateInterface $form_state) {
    if (!isset($this->stepParagraph)) {
      return;
    }

    // Validate the step paragraph.
    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
    $violations = $this->stepParagraph->validate();

    if ($violations->count() > 0) {
      self::setFormErrors($form_state, $violations, [
        'field_title' => $form['step_label'],
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($this->isCreate) {
      $this->session->set(self::SESSION_KEY, $form_state->getValue('step_label'));

      $form_state->setRedirect('va_gov_form_builder.step.step_style', [
        'nid' => $this->digitalForm->id(),
      ]);
    }
    else {
      parent::submitForm($form, $form_state);

      $form_state->setRedirect('va_gov_form_builder.step.home', [
        'nid' => $this->digitalForm->id(),
        'stepParagraphId' => $this->stepParagraph->id(),
      ]);
    }
  }

}
