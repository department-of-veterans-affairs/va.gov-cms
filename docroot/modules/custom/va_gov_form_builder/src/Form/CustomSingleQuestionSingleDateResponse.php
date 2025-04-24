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
class CustomSingleQuestionSingleDateResponse extends FormBuilderPageComponentBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__custom_single_question_single_date_response';
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
      CustomSingleQuestionPageType::SingleDate,
    );

    $form['#theme'] = 'form__va_gov_form_builder__custom_single_question_single_date_response';

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

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date label'),
      '#description' => $this->t('For example, "Anniversary date"'),
      '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_label')[0] ?? '',
      '#required' => TRUE,
    ];

    $form['hint_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hint text for date label'),
      '#default_value' => $this->getComponentParagraphFieldValue('field_digital_form_hint_text')[0] ?? '',
      '#required' => FALSE,
    ];

    $form['preview'] = [
      '#type' => 'html_tag',
      '#tag' => 'img',
      '#attributes' => [
        'src' => self::IMAGE_DIR . 'single-date.png',
        'alt' => $this->t('A preview of the single-date response.'),
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
        'va_gov_form_builder.step.question.custom.date.single_date.page_title',
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
    $dateFormat = $form_state->getValue('date_format');
    $label = $form_state->getValue('label');
    $hintText = $form_state->getValue('hint_text');

    if ($this->isCreate) {
      $this->components[0] = $this->entityTypeManager->getStorage('paragraph')->create([
        'type' => 'digital_form_date_component',
        'field_digital_form_required' => $required,
        'field_digital_form_date_format' => $dateFormat,
        'field_digital_form_label' => $label,
        'field_digital_form_hint_text' => $hintText,
      ]);
    }
    else {
      $this->components[0]->set('field_digital_form_required', $required);
      $this->components[0]->set('field_digital_form_date_format', $dateFormat);
      $this->components[0]->set('field_digital_form_label', $label);
      $this->components[0]->set('field_digital_form_hint_text', $hintText);
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
        'field_digital_form_date_format' => $form['date_format'],
        'field_digital_form_required' => $form['required'],
      ]);
    }
  }

}
