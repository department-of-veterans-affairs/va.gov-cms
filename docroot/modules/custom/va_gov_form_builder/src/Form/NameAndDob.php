<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderBase;

/**
 * Form step for adding name and date of birth.
 */
class NameAndDob extends FormBuilderBase {

  /**
   * Paragraph type accessed by this step.
   *
   * @var string
   */
  private $chapterType = 'digital_form_name_and_date_of_bi';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__name_and_dob';
  }

  /**
   * {@inheritdoc}
   */
  protected function getFields() {
    // We need to add a way to tie form fields (of different names)
    // to the Digital Form fields.
    //
    // Example:
    // If we get an error validating `field_chapters`,
    // we need to tie that to `step_name` on this sub-form.
    //
    // For now, we just return an empty array.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $digitalForm = NULL) {
    $form = parent::buildForm($form, $form_state, $digitalForm);

    $form['name_and_dob_header'] = [
      '#type' => 'html_tag',
      '#tag' => 'h2',
      '#children' => $this->t('Collecting Name and Date of birth'),
    ];

    $form['step_name_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'style' => 'background-color: #E4EFF9; padding: 10px;',
      ],
    ];

    $form['step_name_wrapper']['step_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Step name'),
      '#description' => $this->t('This step name is part of this pattern and not editable.'),
      '#default_value' => $this->t('Your personal information'),
      '#required' => TRUE,
      '#attributes' => [
        'readonly' => 'readonly',
      ],
    ];

    $form['screenshot_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'style' => 'text-align: left;',
      ],
    ];

    $form['screenshot_wrapper']['help_text'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#children' => $this->t('This is how the pattern will appear. The labels
        and fields are not editable.'),
      '#attributes' => [
        'style' => 'margin-top: 20px; margin-bottom: 0px;',
      ],
    ];

    $form['screenshot_wrapper']['screenshot'] = [
      '#type' => 'html_tag',
      '#tag' => 'img',
      '#attributes' => [
        'src' => '/themes/custom/vagovclaro/images/screenshots/name-and-dob.png',
        'alt' => 'Veteran-facing pattern screenshot',
        'style' => 'width: 50%; max-width: 600px; height: auto;',
      ],
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
  protected function setDigitalFormFromFormState(array &$form, FormStateInterface $form_state) {
    // Do not add the name-and-dob chapter if there's one already present.
    if ($this->digitalForm->hasChapterOfType($this->chapterType)) {
      return;
    }

    $nameAndDobParagraph = $this->entityTypeManager->getStorage('paragraph')->create([
      'type' => $this->chapterType,
      'field_title' => $form_state->getValue('step_name'),
      'field_include_date_of_birth' => TRUE,
    ]);
    $this->digitalForm->get('field_chapters')->appendItem($nameAndDobParagraph);

    // Digital Form has been changed.
    $this->digitalFormIsChanged = TRUE;
  }

  /**
   * Submit handler for the 'Back' button.
   */
  public function backButtonSubmitHandler(array &$form, FormStateInterface $form_state) {
    // This will almost certainly change.
    $form_state->setRedirect('va_gov_form_builder.form_info.edit', [
      'nid' => $this->digitalForm->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Only save the form if there have been changes to the Digital Form.
    if ($this->digitalFormIsChanged) {
      parent::submitForm($form, $form_state);
    }

    // For now, redirect to the default node-edit form
    // to confirm creation of the node.
    $form_state->setRedirect('entity.node.edit_form', [
      'node' => $this->digitalForm->id(),
    ]);
  }

}
