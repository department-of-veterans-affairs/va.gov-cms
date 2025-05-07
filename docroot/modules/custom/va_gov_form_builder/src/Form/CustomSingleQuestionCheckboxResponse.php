<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_form_builder\EntityWrapper\DigitalForm;
use Drupal\va_gov_form_builder\Enum\CustomSingleQuestionPageType;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderPageComponentBase;
use Drupal\va_gov_form_builder\Traits\RepeatableFieldGroup;

/**
 * Form step for the defining/editing a Checkbox response.
 */
class CustomSingleQuestionCheckboxResponse extends FormBuilderPageComponentBase {

  use RepeatableFieldGroup;

  /**
   * The maximum number of options that can be added.
   *
   * @var int
   */
  const MAX_REPEATABLE_FIELDS = 7;

  /**
   * The field name of the entity reference for the options paragraph.
   *
   * @var string
   */
  const OPTIONS_FIELD_REF_NAME = 'field_df_response_options';

  /**
   * The number of existing options on the Checkbox component.
   *
   * @var int
   */
  protected int $existingOptionsCount = 0;

  /**
   * The number of existing components on the page paragraph.
   *
   * @var int
   */
  protected int $existingComponentCount = 0;

  /**
   * The Options (field_df_response_options) paragraphs keyed by field delta.
   *
   * @var \Drupal\paragraphs\ParagraphInterface[]
   */
  protected array $options = [];

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'form_builder__custom_single_question_checkbox_response';
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
  ): array {
    $form = parent::buildForm(
      $form,
      $form_state,
      $digitalForm,
      $stepParagraph,
      $pageParagraph,
      CustomSingleQuestionPageType::Checkbox,
    );

    $form['#theme'] = 'form__va_gov_form_builder__custom_single_question_checkbox_response';

    // Titles for the Checkbox Component (digital_form_checkbox).
    $checkboxLabelText = $this->t('List label Checkbox item');
    $checkboxHintText = $this->t('Hint text for item');
    $checkboxRequiredText = $this->t('Required for the submitter');

    // Titles for Checkbox options (digital_form_response_option).
    $checkboxOptionLabelText = $this->t('Label for Checkbox item');
    $checkboxOptionDescriptionText = $this->t('Checkbox description for item');

    // Set existing component count and any existing options.
    $this->existingComponentCount = empty($this->components) ? 0 : count($this->components);
    if ($this->existingComponentCount > 0) {
      if ($this->components[0]->hasField(self::OPTIONS_FIELD_REF_NAME)) {
        /** @var \Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList $optionFieldItemList */
        $optionFieldItemList = $this->components[0]->get(self::OPTIONS_FIELD_REF_NAME);
        $this->options = $optionFieldItemList->referencedEntities();
        $this->existingOptionsCount = count($this->options);
      }
    }

    // We need the checkbox fields on create and edit modes.
    $form['checkbox_fields'] = [
      '#tree' => TRUE,
    ];
    $form['checkbox_fields'][0] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['form-builder-repeatable-field-group']],
      'label' => [
        '#type' => 'textfield',
        '#title' => $checkboxLabelText,
        '#required' => TRUE,
        '#default_value' => '',
      ],
      'hint_text' => [
        '#type' => 'textfield',
        '#title' => $checkboxHintText,
        '#default_value' => '',
      ],
      'required' => [
        '#type' => 'checkbox',
        '#title' => $checkboxRequiredText,
        '#default_value' => TRUE,
      ],
    ];

    if (!$this->isCreate && $this->existingComponentCount > 0) {
      // Set default values for checkbox fields.
      $form['checkbox_fields'][0]['required']['#default_value'] = $this->getComponentParagraphFieldValue('field_digital_form_required')[0] ?? TRUE;
      $form['checkbox_fields'][0]['label']['#default_value'] = $this->getComponentParagraphFieldValue('field_digital_form_label')[0] ?? '';
      $form['checkbox_fields'][0]['hint_text']['#default_value'] = $this->getComponentParagraphFieldValue('field_digital_form_hint_text')[0] ?? '';
      // Add any existing Checkbox options.
      $form['existing_checkbox_option_fields'] = [
        '#tree' => TRUE,
      ];
      foreach ($this->options as $delta => $entity) {
        $form['existing_checkbox_option_fields'][$delta] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['form-builder-repeatable-field-group']],
          'label' => [
            '#type' => 'textfield',
            '#title' => $checkboxOptionLabelText . ' ' . ($delta + 1),
            '#required' => TRUE,
            '#default_value' => $this->getParagraphFieldValue($entity, 'field_digital_form_label'),
          ],
          'description' => [
            '#type' => 'textfield',
            '#title' => $checkboxOptionDescriptionText . ' ' . ($delta + 1),
            '#default_value' => $this->getParagraphFieldValue($entity, 'field_digital_form_description'),
          ],
        ];
      }
    }
    $options_fields_definition = [
      'label' => [
        '#type' => 'textfield',
        '#title' => $checkboxOptionLabelText,
        '#required' => TRUE,
      ],
      'description' => [
        '#type' => 'textfield',
        '#title' => $checkboxOptionDescriptionText,
      ],
    ];

    $this->addRepeatableFieldGroup(
      $form,
      $form_state,
      'dynamic_checkbox_options_fields',
      $options_fields_definition,
      1,
      self::MAX_REPEATABLE_FIELDS,
      $this->existingOptionsCount + 1,
      $this->t('Checkbox Item'),
    );

    $form['preview'] = [
      'no_descriptors' => [
        '#type' => 'html_tag',
        '#tag' => 'img',
        '#attributes' => [
          'src' => self::IMAGE_DIR . 'checkbox-no-descriptors.png',
          'alt' => $this->t('Example without descriptors.png'),
        ],
      ],
      'descriptors' => [
        '#type' => 'html_tag',
        '#tag' => 'img',
        '#attributes' => [
          'src' => self::IMAGE_DIR . 'checkbox-with-descriptors.png',
          'alt' => $this->t('Example with descriptors.png'),
        ],
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
      '#value' => $this->t('Save and continue'),
      '#name' => 'save_and_continue',
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
   * Handler for the edit-question button.
   *
   * @param array $form
   *   The form array values.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function handleEditQuestionClick(array &$form, FormStateInterface $form_state) {
    if ($this->isCreate) {
      $form_state->setRedirect(
        'va_gov_form_builder.step.question.custom.choice.checkbox.page_title',
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
   * Creates a new component based on the provided array of form values.
   *
   * This does not persist data at this point. It creates - in memory - a
   * paragraph entity for the component and sets the appropriate fields.
   *
   * @param array $component
   *   The component form values.
   */
  private function addComponent(array $component) {
    $label = $component['label'] ?? '';
    $hint = $component['hint_text'] ?? '';
    $required = $component['required'] ?? FALSE;

    if ($label !== '') {
      $this->components[0] = Paragraph::create([
        'type' => 'digital_form_checkbox',
        'field_digital_form_label' => $label,
        'field_digital_form_hint_text' => $hint,
        'field_digital_form_required' => $required,
        'field_df_response_options' => $this->options,
      ]);
    }
  }

  /**
   * Add new options.
   *
   * @param array $options
   *   An array of option values.
   * @param int $startIndex
   *   The index/delta to start creating new options at.
   */
  protected function addOptions(array $options, int $startIndex) {
    foreach ($options as $index => $option) {
      $this->options[$index + $startIndex] = Paragraph::create([
        'type' => 'digital_form_response_option',
        'field_digital_form_label' => $option['label'],
        'field_digital_form_description' => $option['description'],
      ]);
    }
  }

  /**
   * Sets the checkbox component from form values.
   *
   * This does not persist data at this point. It updates - in memory - the
   * fields of existing paragraph entity representing the component.
   *
   * @param array $component
   *   The form-state data for the component to update.
   */
  private function setComponent(array $component) {
    if (!isset($this->components[0])) {
      $this->addComponent($component);
    }
    else {
      $label = $component['label'] ?? '';
      $hint = $component['hint_text'] ?? '';
      $required = $component['required'] ?? FALSE;
      $this->components[0]->set('field_digital_form_label', $label);
      $this->components[0]->set('field_digital_form_hint_text', $hint);
      $this->components[0]->set('field_digital_form_required', $required);
      $this->components[0]->set('field_df_response_options', $this->options);
    }
  }

  /**
   * Updates existing options from the provided array.
   *
   * This does not persist data at this point. It updates - in memory - the
   * fields of existing paragraph entities representing the options.
   *
   * @param array $options
   *   The form-state data for the options to update.
   */
  private function updateOptions(array $options) {
    foreach ($options as $delta => $option) {
      $label = $option['label'] ?? '';
      $description = $option['description'] ?? '';
      $this->options[$delta]->set('field_digital_form_label', $label);
      $this->options[$delta]->set('field_digital_form_description', $description);
    }
  }

  /**
   * {@inheritDoc}
   */
  protected function setComponentsFromFormState(FormStateInterface $form_state) {
    // This is critical here in this form because the ajax code that
    // adds dynamic fields calculates the existing components based on
    // $this->components. Without this check here, the value will be incremented
    // when the "+ TEXT INPUT" button is clicked, which will cause issues
    // with rendering the correct number of dynamic fields.
    $clickedButton = $form_state->getTriggeringElement()['#name'] ?? '';
    if ($clickedButton !== 'save_and_continue') {
      return;
    }
    $existingOptions = $form_state->getValue('existing_checkbox_option_fields') ?? [];
    $checkbox = $form_state->getValue('checkbox_fields')[0] ?? [];
    $newOptions = $form_state->getValue('dynamic_checkbox_options_fields') ?? [];

    // This is the index at which to start adding new options.
    $nextIndex = 0;

    // In edit mode, we need to first update existing options, then add new
    // options. Additionally, when we change or add options, we also need to
    // update the Checkbox component.
    if (!$this->isCreate) {
      if (count($existingOptions) > 0) {
        $this->updateOptions($existingOptions);
        $nextIndex = count($existingOptions);
      }
    }
    // In both create and edit modes, we need to create
    // new options if they were added by the user. We need to start adding new
    // options at the next index.
    if (count($newOptions) > 0) {
      $this->addOptions($newOptions, $nextIndex);
    }
    // Set the Checkbox component. This will update or create as necessary.
    $this->setComponent($checkbox);
  }

  /**
   * {@inheritDoc}
   */
  protected function validateComponents(array $form, FormStateInterface $form_state) {
    // Since we are using only a single component, there is no need to iterate
    // over all components.
    if (empty($this->components)) {
      return;
    }
    $this->validateComponent(0, $form, $form_state);
    $this->validateOptions($form, $form_state);
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
      $field = $form['checkbox_fields'][$i];
      self::setFormErrors($form_state, $violations, [
        'field_digital_form_label' => $field['label'],
        'field_digital_form_hint_text' => $field['hint_text'],
        'field_digital_form_required' => $field['required'],
      ]);
    }
  }

  /**
   * Validate Checkbox options.
   *
   * @param array $form
   *   The form values array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  protected function validateOptions(array $form, FormStateInterface $form_state) {
    foreach ($this->options as $delta => $paragraph) {
      $violations = $paragraph->validate();
      if ($violations->count() > 0) {
        if ($delta < $this->existingOptionsCount) {
          // This is an existing option.
          $field = $form['existing_checkbox_option_fields'][$delta];
        }
        else {
          // This is a new option that was added on this submission.
          $field = $form['dynamic_checkbox_options_fields_fieldset']['dynamic_checkbox_options_fields'][$delta - $this->existingOptionsCount];
        }
        self::setFormErrors($form_state, $violations, [
          'field_digital_form_label' => $field['label'],
          'field_digital_form_description' => $field['description'],
        ]);
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the options.
    foreach ($this->options as $paragraph) {
      $paragraph->save();
    }
    parent::submitForm($form, $form_state);
  }

}
