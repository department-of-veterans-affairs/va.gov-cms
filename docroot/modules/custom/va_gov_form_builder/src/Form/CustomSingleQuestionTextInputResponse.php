<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_form_builder\EntityWrapper\DigitalForm;
use Drupal\va_gov_form_builder\Enum\CustomSingleQuestionPageType;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderPageComponentBase;
use Drupal\va_gov_form_builder\Traits\RepeatableFieldGroup;

/**
 * Form step for the defining/editing a text-input response.
 */
class CustomSingleQuestionTextInputResponse extends FormBuilderPageComponentBase {

  use RepeatableFieldGroup;

  /**
   * The maximum number of text-input fields that can be added.
   *
   * @var int
   */
  const MAX_REPEATABLE_FIELDS = 7;

  /**
   * The number of existing components on the page paragraph.
   *
   * @var int
   */
  protected $existingComponentCount;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__custom_single_question_text_input_response';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state,
    DigitalForm|null $digitalForm = NULL,
    Paragraph|null $stepParagraph = NULL,
    Paragraph|null $pageParagraph = NULL,
    CustomSingleQuestionPageType|null $pageComponentType = NULL,
  ) {
    $form = parent::buildForm(
      $form,
      $form_state,
      $digitalForm,
      $stepParagraph,
      $pageParagraph,
      CustomSingleQuestionPageType::TextInput,
    );

    $form['#theme'] = 'form__va_gov_form_builder__custom_single_question_text_input_response';

    $labelText = 'Label for Text-input item';
    $hintText = 'Hint text for item';
    $this->existingComponentCount = empty($this->components) ? 0 : count($this->components);

    if (!$this->isCreate && $this->existingComponentCount > 0) {
      // In edit mode, we need to render the existing components.
      $form['existing_text_input_fields'] = [
        '#tree' => TRUE,
      ];
      foreach ($this->components as $i => $component) {
        $form['existing_text_input_fields'][$i] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['form-builder-repeatable-field-group']],
          'label' => [
            '#type' => 'textfield',
            '#title' => $labelText . ' ' . ($i + 1),
            '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_label')[$i] ?? '',
            '#required' => TRUE,
          ],
          'hint_text' => [
            '#type' => 'textfield',
            '#title' => $hintText . ' ' . ($i + 1),
            '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_hint_text')[$i] ?? '',
          ],
          'required' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Required for the submitter'),
            '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_required')[$i] ?? TRUE,
          ],
        ];
      }
    }

    $repeatableTextInputFieldDefinitions = [
      'label' => [
        '#type' => 'textfield',
        '#title' => $labelText,
        '#required' => TRUE,
      ],
      'hint_text' => [
        '#type' => 'textfield',
        '#title' => $hintText,
      ],
      'required' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Required for the submitter'),
        '#default_value' => FALSE,
        '#displayIndexCount' => FALSE,
      ],
    ];
    $this->addRepeatableFieldGroup(
      $form,
      $form_state,
      'dynamic_text_input_fields',
      $repeatableTextInputFieldDefinitions,
      1,
      self::MAX_REPEATABLE_FIELDS,
      $this->existingComponentCount + 1,
      'Text Input',
    );

    $form['preview'] = [
      '#type' => 'html_tag',
      '#tag' => 'img',
      '#attributes' => [
        'src' => self::IMAGE_DIR . 'text-input.png',
        'alt' => $this->t('A preview of the text-input response.'),
      ],
    ];

    $form['actions']['edit-question'] = [
      '#type' => 'submit',
      '#value' => $this->t('Edit question'),
      '#name' => 'edit-question',
      '#attributes' => [
        'class' => [
          'button',
          'button--secondary',
          'form-submit',
        ],
        'formnovalidate' => 'formnovalidate',
      ],
      '#submit' => ['::handleEditQuestionClick'],
      '#limit_validation_errors' => [],
      '#weight' => '10',
    ];

    $form['actions']['save_and_continue'] = [
      '#type' => 'submit',
      '#name' => 'save_and_continue',
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
   * Handler for the edit-question button.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function handleEditQuestionClick(array &$form, FormStateInterface $form_state) {
    if ($this->isCreate) {
      $form_state->setRedirect(
        'va_gov_form_builder.step.question.custom.text.text_input.page_title',
        [
          'nid' => $this->digitalForm->id(),
          'stepParagraphId' => $this->stepParagraph->id(),
        ],
      );
    }
    else {
      $form_state->setRedirect(
        'va_gov_form_builder.step.question.page_title',
        [
          'nid' => $this->digitalForm->id(),
          'stepParagraphId' => $this->stepParagraph->id(),
          'pageParagraphId' => $this->pageParagraph->id(),
        ]
      );
    }
  }

  /**
   * Creates new components based on the provided array of components.
   *
   * This does not persist data at this point. It creates - in memory - a
   * paragraph entity for each component and sets the appropriate fields.
   *
   * @param array $components
   *   The form-state data for the components to create.
   * @param int $startIndex
   *   The index at which to start adding new components.
   */
  private function addComponents(array $components, int $startIndex) {
    foreach ($components as $i => $component) {
      $label = $component['label'] ?? '';
      $hint = $component['hint_text'] ?? '';
      $required = $component['required'] ?? FALSE;

      // Ignore dynamic fields that were added but not filled out.
      // This should never be the case, as the form should not pass
      // client-side validation if they are empty, but we check
      // here for good measure.
      if ($label !== '') {
        $this->components[$startIndex + $i] = $this->entityTypeManager->getStorage('paragraph')->create([
          'type' => 'digital_form_text_input',
          'field_digital_form_label' => $label,
          'field_digital_form_hint_text' => $hint,
          'field_digital_form_required' => $required,
        ]);
      }
    }
  }

  /**
   * Updates existing components based on the provided array of components.
   *
   * This does not persist data at this point. It updates - in memory - the
   * fields of existing paragraph entities representing the components.
   *
   * @param array $components
   *   The form-state data for the components to update.
   */
  private function updateComponents(array $components) {
    foreach ($components as $i => $component) {
      if (isset($this->components[$i])) {
        $label = $component['label'] ?? '';
        $hint = $component['hint_text'] ?? '';
        $required = $component['required'] ?? FALSE;

        $this->components[$i]->set('field_digital_form_label', $label);
        $this->components[$i]->set('field_digital_form_hint_text', $hint);
        $this->components[$i]->set('field_digital_form_required', $required);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function setComponentsFromFormState(FormStateInterface $form_state) {
    // Only set components if the save-and-continue button was clicked.
    // This is critical here in this form because the ajax code that
    // adds dynamic fields calculates the existing components based on
    // $this->components. Without this check here, the value will be incremented
    // when the "+ TEXT INPUT" button is clicked, which will cause issues
    // with rendering the correct number of dynamic fields.
    $clickedButton = $form_state->getTriggeringElement()['#name'] ?? '';
    if ($clickedButton !== 'save_and_continue') {
      return;
    }

    // This is the index at which to start adding new components.
    $nextIndex = 0;

    // In edit mode, we need to first update existing components
    // and determine how many exist so we can continue adding new
    // components at the next index.
    if (!$this->isCreate) {
      $existingComponents = $form_state->getValue('existing_text_input_fields') ?? [];
      if (count($existingComponents) > 0) {
        $this->updateComponents($existingComponents);
        $nextIndex = count($existingComponents);
      }
    }

    // In both create and edit modes, we need to create
    // new components if they were added by the user.
    // We need to start adding new components at the next index.
    $newComponents = $form_state->getValue('dynamic_text_input_fields') ?? [];
    if (count($newComponents) > 0) {
      $this->addComponents($newComponents, $nextIndex);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function validateComponent(int $i, array $form, FormStateInterface $form_state) {
    if (!isset($this->components[$i])) {
      return;
    }

    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
    $violations = $this->components[$i]->validate();

    if ($violations->count() > 0) {
      if ($i < $this->existingComponentCount) {
        // This is an existing component.
        $field = $form['existing_text_input_fields'][$i];
      }
      else {
        // This is a new component that was added on this submission.
        $field = $form['dynamic_text_input_fields_fieldset']['dynamic_text_input_fields'][$i - $this->existingComponentCount];
      }
      self::setFormErrors($form_state, $violations, [
        'field_digital_form_label' => $field['label'],
        'field_digital_form_hint_text' => $field['hint_text'],
        'field_digital_form_required' => $field['required'],
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /*
     * In create mode, nothing is different from the parent class.
     */
    if ($this->isCreate) {
      parent::submitForm($form, $form_state);
    }

    /*
     * In edit mode, this is different from the parent class.
     * Here, we have not only existing components that
     * need to be saved, but also potentially new components
     * that need to be appended to the page paragraph.
     *
     * So, we need to:
     * 1. Save existing components.
     * 2. Add new components if they were added by the user.
     */
    else {
      // Save existing components.
      for ($i = 0; $i < $this->existingComponentCount; $i++) {
        if (isset($this->components[$i])) {
          $this->components[$i]->save();
        }
      }

      // Add new components if they were added by the user.
      if (count($this->components) > $this->existingComponentCount) {
        for ($i = $this->existingComponentCount; $i < count($this->components); $i++) {
          $this->pageParagraph
            ->get('field_digital_form_components')
            ->appendItem($this->components[$i]);
        }

        // Save the page paragraph.
        $this->pageParagraph->save();
      }

      // Redirect user to next page.
      $this->redirectOnSuccess($form_state);
    }
  }

}
