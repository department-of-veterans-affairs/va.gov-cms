<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderStepBase;

/**
 * Form step for the selecting a step style.
 */
class StepStyle extends FormBuilderStepBase {

  /**
   * The label of the step.
   *
   * @var string
   */
  protected $stepLabel;

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
  public function buildForm(array $form, FormStateInterface $form_state, $digitalForm = NULL, $stepParagraph = NULL) {
    // On this form, the step paragraph should be allowed to be empty,
    // since it is always in "create" mode.
    $this->allowEmptyStepParagraph = TRUE;
    $this->isCreate = TRUE;

    // Grab the previously entered step label from stession storage.
    $this->stepLabel = $this->session->get('form_builder:add_step:step_label');

    // Call parent build method.
    $form = parent::buildForm($form, $form_state, $digitalForm, $stepParagraph);

    // Build the form.
    $form['#theme'] = 'form__va_gov_form_builder__step_style';

    $form['step_label']['label'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->stepLabel,
    ];
    $form['step_label']['edit_button'] = [
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

    $form['preview']['single_question'] = [
      '#type' => 'html_tag',
      '#tag' => 'img',
      '#attributes' => [
        'src' => self::IMAGE_DIR . 'single-question.png',
        'alt' => $this->t('Single-question preview'),
      ],
    ];
    $form['preview']['repeating_set'] = [
      '#type' => 'html_tag',
      '#tag' => 'img',
      '#attributes' => [
        'src' => self::IMAGE_DIR . 'repeating-set.png',
        'alt' => $this->t('Repeating-set preview'),
      ],
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
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function handleEditStepLabelClick(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('va_gov_form_builder.step.step_label.create', [
      'nid' => $this->digitalForm->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function setStepParagraphFromFormState(FormStateInterface $form_state) {
    if ($this->stepStyle === 'repeating') {
      $this->stepParagraph = $this->entityTypeManager->getStorage('paragraph')->create([
        'type' => 'digital_form_list_loop',
        'field_title' => $this->stepLabel,
      ]);
    }
    else {
      $this->stepParagraph = $this->entityTypeManager->getStorage('paragraph')->create([
        'type' => 'digital_form_custom_step',
        'field_title' => $this->stepLabel,
      ]);
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

    foreach ($violations as $violation) {
      $fieldName = self::getViolationFieldName($violation);

      if ($fieldName === 'field_title') {
        // This is a violation on the step label. This should not be
        // possible as the step label should have been validated before
        // this point, but we check here just in case. If there is an error,
        // set an error on the form itself rather than an individual field.
        $form_state->setError(
          $form,
          $this->t('There was an error with the step label. Return to the previous page and adjust as needed.'),
        );
      }
      elseif ($fieldName === 'field_digital_form_pages') {
        // Do nothing. This is allowed to be empty here, despite it being a
        // validation error. It *should* always be empty at this point
        // of creating the step.
      }
      else {
        // Some other error. Again, this should not be possible, but we check
        // here just in case. If there is an error, set an error on the
        // form itself rather than an individual field.
        $form_state->setError(
          $form,
          $this->t('There was an error. Please check all the fields and try again.'),
        );
      }
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

    // Clear the session variable after successful paragraph save.
    $this->session->set('form_builder:add_step:step_label', NULL);

    $form_state->setRedirect('va_gov_form_builder.step.layout', [
      'nid' => $this->digitalForm->id(),
      'stepParagraphId' => $this->stepParagraph->id(),
    ]);
  }

}
