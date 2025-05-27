<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_form_builder\EntityWrapper\DigitalForm;
use Drupal\va_gov_form_builder\Enum\CustomSingleQuestionPageType;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderPageBase;

/**
 * Form step for the defining/editing a page title.
 *
 * This also allows defining/editing the page body content,
 * and could inlcude additional fields in the future, but
 * referring to it as "page title" seems to be the simplest
 * language to capture the intent of this form.
 */
class CustomSingleQuestionPageTitle extends FormBuilderPageBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__custom_single_question_page_title';
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
      $pageComponentType
    );

    if (empty($pageParagraph)) {
      // If no page paragraph is passed in, this is "create" mode.
      $this->isCreate = TRUE;
      $defaultValues = $this->session->get(self::SESSION_KEY) ?? [
        'title' => '',
        'body' => '',
      ];
    }
    else {
      // If a page paragraph is passed in, this is "edit" mode.
      $this->isCreate = FALSE;
      $defaultValues = [
        'title' => $this->getPageParagraphFieldValue('field_title') ?? '',
        'body' => $this->getPageParagraphFieldValue('field_digital_form_body_text') ?? '',
      ];
    }

    $form['#theme'] = 'form__va_gov_form_builder__custom_single_question_page_title';

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Page title (Question content)'),
      '#description' => $this->t('What is the question you are asking? This could also be a statement, identifying the information to input next.'),
      '#required' => TRUE,
      '#default_value' => $defaultValues['title'],
    ];

    $form['body_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Body content'),
      '#description' => $this->t('Additional explanation, if needed, about this question or statement.'),
      '#required' => FALSE,
      '#default_value' => $defaultValues['body'],
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
  protected function setPageParagraphFromFormState(FormStateInterface $form_state) {
    $title = $form_state->getValue('title');
    $body = $form_state->getValue('body_text');

    if ($this->isCreate) {
      $this->pageParagraph = $this->entityTypeManager->getStorage('paragraph')->create([
        'type' => 'digital_form_page',
        'field_title' => $title,
        'field_digital_form_body_text' => $body,
      ]);
    }
    else {
      $this->pageParagraph->set('field_title', $title);
      $this->pageParagraph->set('field_digital_form_body_text', $body);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function validatePageParagraph(array $form, FormStateInterface $form_state) {
    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
    $violations = $this->pageParagraph->validate();
    if ($violations->count() > 0) {
      $this->setFormErrors($form_state, $violations, [
        'field_title' => $form['title'],
        'field_digital_form_body_text' => $form['body_text'],
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($this->isCreate) {
      $this->session->set(
        self::SESSION_KEY,
        [
          'title' => $form_state->getValue('title'),
          'body' => $form_state->getValue('body_text'),
        ]
      );

      $form_state->setRedirect(
        "va_gov_form_builder.step.question.custom." . $this->pageComponentType->value . ".response",
        [
          'nid' => $this->digitalForm->id(),
          'stepParagraphId' => $this->stepParagraph->id(),
        ],
      );
    }
    else {
      parent::submitForm($form, $form_state);

      $form_state->setRedirect(
        "va_gov_form_builder.step.question.response",
        [
          'nid' => $this->digitalForm->id(),
          'stepParagraphId' => $this->stepParagraph->id(),
          'pageParagraphId' => $this->pageParagraph->id(),
        ],
      );
    }
  }

}
