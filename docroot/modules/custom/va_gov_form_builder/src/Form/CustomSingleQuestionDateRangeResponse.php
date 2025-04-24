<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_form_builder\EntityWrapper\DigitalForm;
use Drupal\va_gov_form_builder\Enum\CustomSingleQuestionPageType;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderPageComponentBase;

/**
 * Form step for the defining/editing a date-range response.
 */
class CustomSingleQuestionDateRangeResponse extends FormBuilderPageComponentBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__custom_single_question_date_range_response';
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
      CustomSingleQuestionPageType::DateRange,
    );

    $form['#theme'] = 'form__va_gov_form_builder__custom_single_question_date_range_response';

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

    $form['components'] = [
      [
        'label' => [
          '#type' => 'textfield',
          '#title' => $this->t('Date label 1'),
          '#description' => $this->t('For example, "Start date of employment"'),
          '#required' => TRUE,
          '#parents' => ['components', '0', 'label'],
          '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_label')[0] ?? '',
        ],
        'hint_text' => [
          '#type' => 'textfield',
          '#title' => $this->t('Hint text for date label 1'),
          '#required' => FALSE,
          '#parents' => ['components', '0', 'hint_text'],
          '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_hint_text')[0] ?? '',
        ],
      ],
      [
        'label' => [
          '#type' => 'textfield',
          '#title' => $this->t('Date label 2'),
          '#description' => $this->t('For example, "End date of employment"'),
          '#required' => TRUE,
          '#parents' => ['components', '1', 'label'],
          '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_label')[1] ?? '',
        ],
        'hint_text' => [
          '#type' => 'textfield',
          '#title' => $this->t('Hint text for date label 2'),
          '#required' => FALSE,
          '#parents' => ['components', '1', 'hint_text'],
          '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_hint_text')[1] ?? '',
        ],
      ],
    ];

    $form['preview'] = [
      '#type' => 'html_tag',
      '#tag' => 'img',
      '#attributes' => [
        'src' => self::IMAGE_DIR . 'date-range.png',
        'alt' => $this->t('A preview of the date-range response.'),
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
        'va_gov_form_builder.step.question.custom.date.date_range.page_title',
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
    $required = $form_state->getValue('required') ?? FALSE;
    $dateFormat = 'month_day_year';
    $components = $form_state->getValue('components');

    $label[0] = $components[0]['label'] ?? '';
    $hint[0] = $components[0]['hint_text'] ?? '';
    $label[1] = $components[1]['label'] ?? '';
    $hint[1] = $components[1]['hint_text'] ?? '';

    if ($this->isCreate) {
      for ($i = 0; $i <= 1; $i++) {
        $this->components[$i] = $this->entityTypeManager->getStorage('paragraph')->create([
          'type' => 'digital_form_date_component',
          'field_digital_form_required' => $required,
          'field_digital_form_date_format' => $dateFormat,
          'field_digital_form_label' => $label[$i],
          'field_digital_form_hint_text' => $hint[$i],
        ]);
      }
    }
    else {
      for ($i = 0; $i <= 1; $i++) {
        $this->components[$i]->set('field_digital_form_required', $required);
        $this->components[$i]->set('field_digital_form_date_format', $dateFormat);
        $this->components[$i]->set('field_digital_form_label', $label[$i]);
        $this->components[$i]->set('field_digital_form_hint_text', $hint[$i]);
      }
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
        'field_digital_form_required' => $form['required'],
        'field_digital_form_label' => $form['components'][$i]['label'],
        'field_digital_form_hint_text' => $form['components'][$i]['hint_text'],
      ]);
    }
  }

}
