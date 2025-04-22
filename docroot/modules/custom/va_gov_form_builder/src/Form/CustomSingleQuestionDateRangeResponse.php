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

    // @todo Set form theme.
    $form['page_title'] = [
      '#markup' => $this->pageData['title'],
    ];

    $form['page_body'] = [
      '#markup' => $this->pageData['body'],
    ];

    $form['field_digital_form_required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Required for the submitter'),
      '#required' => TRUE,
      '#default_value' => TRUE,
    ];

    $form['field_digital_form_date_format'] = [
      '#type' => 'radios',
      '#title' => $this->t('Date format'),
      '#options' => [
        'month_day_year' => $this->t('A memorable date that a submitter knows'),
        'month_year' => $this->t('A date that a submitter can approximate'),
      ],
      '#default_value' => 'month_day_year',
      '#required' => TRUE,
    ];

    $form['components'] = [
      [
        'field_digital_form_label' => [
          '#type' => 'textfield',
          '#title' => $this->t('Date label'),
          '#description' => $this->t('For example, "Anniversary date"'),
          '#required' => TRUE,
          '#parents' => ['components', '0', 'field_digital_form_label'],
        ],
        'field_digital_form_hint_text' => [
          '#type' => 'textfield',
          '#title' => $this->t('Hint text for date label'),
          '#required' => FALSE,
          '#parents' => ['components', '0', 'field_digital_form_hint_text'],
        ],
      ],
      [
        'field_digital_form_label' => [
          '#type' => 'textfield',
          '#title' => $this->t('Date label'),
          '#description' => $this->t('For example, "Anniversary date"'),
          '#required' => TRUE,
          '#parents' => ['components', '1', 'field_digital_form_label'],
        ],
        'field_digital_form_hint_text' => [
          '#type' => 'textfield',
          '#title' => $this->t('Hint text for date label'),
          '#required' => FALSE,
          '#parents' => ['components', '1', 'field_digital_form_hint_text'],
        ],
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
  protected function setComponentsFromFormState(FormStateInterface $form_state) {
    // Set the date component paragraph.
    $this->components[0] = $this->entityTypeManager->getStorage('paragraph')->create([
      'type' => 'digital_form_date_component',
      'field_digital_form_date_format' => 'month_day_year',
      'field_digital_form_hint_text' => 'hint text',
      'field_digital_form_label' => 'asdf',
      'field_digital_form_required' => TRUE,
    ]);
    $this->components[1] = $this->entityTypeManager->getStorage('paragraph')->create([
      'type' => 'digital_form_date_component',
      'field_digital_form_date_format' => 'month_day_year',
      'field_digital_form_hint_text' => 'hint text',
      'field_digital_form_label' => 'asdf',
      'field_digital_form_required' => TRUE,
    ]);
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
      self::setFormErrors($form, $form_state, $violations, [
        'field_digital_form_label' => $form['components'][$i]['field_digital_form_label'],
        'field_digital_form_hint_text' => $form['components'][$i]['field_digital_form_hint_text'],
        'field_digital_form_date_format' => $form['field_digital_form_date_format'],
        'field_digital_form_required' => $form['field_digital_form_required'],
      ]);
    }
  }

}
