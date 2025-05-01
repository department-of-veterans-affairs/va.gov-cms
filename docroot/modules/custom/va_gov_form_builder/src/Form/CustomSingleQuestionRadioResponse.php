<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_form_builder\EntityWrapper\DigitalForm;
use Drupal\va_gov_form_builder\Enum\CustomSingleQuestionPageType;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderPageComponentBase;

/**
 * Form step for the defining/editing a single-date response.
 */
class CustomSingleQuestionRadioResponse extends FormBuilderPageComponentBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__custom_single_question_radio_response';
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
      CustomSingleQuestionPageType::Radio,
    );

    $form['#theme'] = 'form__va_gov_form_builder__custom_single_question_radio_response';

    $form['page_title'] = [
      '#markup' => $this->pageData['title'],
    ];

    $form['page_body'] = [
      '#markup' => $this->pageData['body'],
    ];

    $form['required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Required for the submitter'),
      '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_required')[0] ?? TRUE,
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('List label'),
      '#description' => $this->t('For example, "Anniversary date"'),
      '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_label')[0] ?? '',
      '#required' => TRUE,
    ];

    $form['hint_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Optional Hint text for list label'),
      '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_hint_text')[0] ?? '',
      '#required' => FALSE,
      '#suffix' => '<hr/>',
    ];

    $form['options'] = [
      '#type' => 'container',
      '#weight' => -10,
      '#tree' => TRUE,
    ];
    $options_field_definitions = [
      'label' => [
        '#type' => 'textfield',
        '#title' => 'Radio label',
        '#required' => TRUE,
        '#default_value' => '',
      ],
      'description' => [
        '#type' => 'textfield',
        '#title' => 'Radio description',
        '#default_value' => '',
        '#suffix' => '<hr/>',
      ],
    ];

    $component = $this->components[0];
    /** @var \Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList $radioOptions */
    $radioOptions = $component->get('field_df_response_options');
    foreach ($radioOptions as $delta => $option) {
      $form['options'][$delta] = $options_field_definitions;
      $form['options'][$delta]['label']['#title'] = $this->t('Radio label for item @delta', ['@delta' => $delta + 1]);
      $form['options'][$delta]['label']['#default_value'] = $option->entity->get('field_digital_form_label')->value;
      $form['options'][$delta]['description']['#title'] = $this->t('Radio description for item @delta', ['@delta' => $delta + 1]);
      $form['options'][$delta]['description']['#default_value'] = $option->entity->get('field_digital_form_description')->value;
    }
    $form['preview'] = [
      'no_descriptors' => [
        '#type' => 'html_tag',
        '#tag' => 'img',
        '#attributes' => [
          'src' => self::IMAGE_DIR . 'radio-no-descriptors.png',
          'alt' => $this->t('Example without descriptors.png'),
        ],
      ],
      'descriptors' => [
        '#type' => 'html_tag',
        '#tag' => 'img',
        '#attributes' => [
          'src' => self::IMAGE_DIR . 'radio-with-descriptors.png',
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
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function handleEditQuestionClick(array &$form, FormStateInterface $form_state) {
    if ($this->isCreate) {
      $form_state->setRedirect(
        'va_gov_form_builder.step.question.custom.choice.radio.page_title',
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
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function setComponentsFromFormState(FormStateInterface $form_state) {
    $required = $form_state->getValue('required') ?? FALSE;
    $label = $form_state->getValue('label');
    $hintText = $form_state->getValue('hint_text');
    $formOptions = $form_state->getValue('options');

    if ($this->isCreate) {
      $optionParagraphs = [];
      foreach ($formOptions as $delta => $option) {
        $optionParagraphs[$delta] = Paragraph::create([
          'type' => 'digital_form_response_option',
          'field_digital_form_label' => $option[$delta]['label'],
          'field_digital_form_description' => $option[$delta]['description'],
        ]);
      }
      $this->components[0] = $this->entityTypeManager->getStorage('paragraph')->create([
        'type' => 'digital_form_radio_button',
        'field_digital_form_required' => $required,
        'field_digital_form_label' => $label,
        'field_digital_form_hint_text' => $hintText,
        'field_df_response_options' => $optionParagraphs,
      ]);
    }
    else {
      /** @var \Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList $radioOptions */
      $radioOptions = $this->components[0]->get('field_df_response_options');
      foreach ($radioOptions as $delta => $option) {
        $option->entity->set('field_digital_form_label', $formOptions[$delta]['label'] ?? []);
        $option->entity->set('field_digital_form_description', $formOptions[$delta]['description'] ?? []);
      }
      $this->components[0]->set('field_digital_form_label', $label);
      $this->components[0]->set('field_digital_form_hint_text', $hintText);
      $this->components[0]->set('field_digital_form_required', $required);
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
      self::setFormErrors($form_state, $violations, [
        'field_digital_form_label' => $form['label'],
        'field_digital_form_hint_text' => $form['hint_text'],
        'field_digital_form_required' => $form['required'],
      ]);
    }

    // Validate existing radio options.
    $radioOptions = $this->components[$i]->get('field_df_response_options');
    foreach ($radioOptions as $delta => $option) {
      $optionViolations = $option->entity->validate();
      if ($optionViolations->count() > 0) {
        self::setFormErrors($form_state, $optionViolations, [
          'field_digital_form_label' => $form['options'][$delta]['label'],
          'field_digital_form_description' => $form['options'][$delta]['label'],
        ]);
      }
    }

    // @todo Validate new items. Perhaps we need to append them to the field first?
  }

}
