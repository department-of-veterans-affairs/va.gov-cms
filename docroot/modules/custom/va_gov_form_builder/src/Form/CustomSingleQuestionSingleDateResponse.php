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
      '#required' => TRUE,
      '#default_value' => TRUE,
    ];

    $form['date_format'] = [
      '#type' => 'radios',
      '#title' => $this->t('Date format'),
      '#options' => [
        'month_day_year' => $this->t('A memorable date that a submitter knows'),
        'month_year' => $this->t('A date that a submitter can approximate'),
      ],
      '#default_value' => 'month_day_year',
      '#required' => TRUE,
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date label'),
      '#description' => $this->t('For example, "Anniversary date"'),
      '#required' => TRUE,
    ];

    $form['hint_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hint text for date label'),
      '#required' => FALSE,
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
    $this->components[0] = $this->entityTypeManager->getStorage('paragraph')->create([
      'type' => 'digital_form_date_component',
      'field_digital_form_date_format' => $form_state->getValue('date_format'),
      'field_digital_form_hint_text' => $form_state->getValue('hint_text'),
      'field_digital_form_label' => $form_state->getValue('label'),
      'field_digital_form_required' => $form_state->getValue('required'),
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
      self::setFormErrors($form_state, $violations, [
        'field_digital_form_label' => $form['label'],
        'field_digital_form_hint_text' => $form['hint_text'],
        'field_digital_form_date_format' => $form['date_format'],
        'field_digital_form_required' => $form['required'],
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
