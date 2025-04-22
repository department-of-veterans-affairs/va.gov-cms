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

    $form['date_format'] = [
      '#type' => 'va_gov_form_builder__expanded_radios',
      '#title' => $this->t('This date type is:'),
      '#options' => [
        'month_day_year' => $this->t('A memorable date that a submitter knows (includes Month, Day, Year)'),
        'month_year' => $this->t('A date that a submitter can approximate (includes Month, Year)'),
      ],
      '#options_expanded_content' => [
        'month_day_year' => [
          '#markup' => '<p><a href="" target="_blank">View example in Sample Forms</a></p>',
        ],
        'month_year' => [
          '#markup' => '<p><a href="" target="_blank">View example in Sample Forms</a></p>',
        ],
      ],
      '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_date_format')[0] ?? 'month_day_year',
    ];

    $form['components'] = [
      [
        'label' => [
          '#type' => 'textfield',
          '#title' => $this->t('Date label'),
          '#description' => $this->t('For example, "Anniversary date"'),
          '#required' => TRUE,
          '#parents' => ['components', '0', 'label'],
          '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_label')[0] ?? '',
        ],
        'hint_text' => [
          '#type' => 'textfield',
          '#title' => $this->t('Hint text for date label'),
          '#required' => FALSE,
          '#parents' => ['components', '0', 'hint_text'],
          '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_hint_text')[0] ?? '',
        ],
      ],
      [
        'label' => [
          '#type' => 'textfield',
          '#title' => $this->t('Date label'),
          '#description' => $this->t('For example, "Anniversary date"'),
          '#required' => TRUE,
          '#parents' => ['components', '1', 'label'],
          '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_label')[1] ?? '',
        ],
        'hint_text' => [
          '#type' => 'textfield',
          '#title' => $this->t('Hint text for date label'),
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

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    xdebug_var_dump('submit');
    exit;
  }

}
