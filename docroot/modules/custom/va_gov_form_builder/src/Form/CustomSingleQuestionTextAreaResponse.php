<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_form_builder\EntityWrapper\DigitalForm;
use Drupal\va_gov_form_builder\Enum\CustomSingleQuestionPageType;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderPageComponentBase;

/**
 * Form step for the defining/editing a text-area response.
 */
class CustomSingleQuestionTextAreaResponse extends FormBuilderPageComponentBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__custom_single_question_text_area_response';
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
      CustomSingleQuestionPageType::TextArea,
    );

    $form['#theme'] = 'form__va_gov_form_builder__custom_single_question_text_area_response';

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Labe for Text area'),
      '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_label')[0] ?? '',
      '#required' => TRUE,
    ];

    $form['hint_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hint text for Text area'),
      '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_hint_text')[0] ?? '',
      '#required' => FALSE,
    ];

    $form['required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Required for the submitter'),
      '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_required')[0] ?? TRUE,
    ];

    $form['preview'] = [
      '#type' => 'html_tag',
      '#tag' => 'img',
      '#attributes' => [
        'src' => self::IMAGE_DIR . 'text-area.png',
        'alt' => $this->t('A preview of the text-area response.'),
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
        'va_gov_form_builder.step.question.custom.text.text_area.page_title',
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
   */
  protected function setComponentsFromFormState(FormStateInterface $form_state) {
    $label = $form_state->getValue('label');
    $hintText = $form_state->getValue('hint_text');
    $required = $form_state->getValue('required') ?? FALSE;

    if ($this->isCreate) {
      $this->components[0] = $this->entityTypeManager->getStorage('paragraph')->create([
        'type' => 'digital_form_text_area',
        'field_digital_form_label' => $label,
        'field_digital_form_hint_text' => $hintText,
        'field_digital_form_required' => $required,
      ]);
    }
    else {
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
  }

}
