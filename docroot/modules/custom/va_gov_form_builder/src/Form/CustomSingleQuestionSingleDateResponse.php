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
  protected function getFields() {
    return [
      self::FIELD_KEYS['title'],
      self::FIELD_KEYS['body'],
    ];
  }

  /**
   * Returns a list of fields on the child component.
   */
  protected function getComponentFields() {
    return [
      'field_digital_form_date_format',
      'field_digital_form_hint_text',
      'field_digital_form_label',
      'field_digital_form_required',
    ];
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

    if (empty($pageParagraph)) {
      // If no page paragraph is passed in, this is "create" mode.
      $this->isCreate = TRUE;
      $defaultValues = $this->session->get(self::SESSION_KEY);
    }
    else {
      // If a page paragraph is passed in, this is "edit" mode.
      $this->isCreate = FALSE;
      $defaultValues = [
        'title' => $this->getPageParagraphFieldValue(self::FIELD_KEYS['title']),
        'body' => $this->getPageParagraphFieldValue(self::FIELD_KEYS['body']),
      ];
    }

    $form['#theme'] = 'form__va_gov_form_builder__custom_single_question_single_date_response';

    $form['field_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('TEMPORARY QUESTION'),
      '#description' => $this->t('What is the...'),
      '#required' => TRUE,
      '#default_value' => $defaultValues['title'],
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
      'field_digital_form_hint_text ' => 'hint text',
      'field_digital_form_label' => 'label',
      'field_digital_form_required' => TRUE,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    xdebug_var_dump('submit');
  }

}
