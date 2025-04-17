<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderPageBase;

/**
 * Form step for the defining/editing a page title.
 *
 * This also allows defining/editing the page body content,
 * and could inlcude additional fields in the future, but
 * referring to it as "page title" seems to be the simplest
 * language to capture the intent of this form.
 */
class PageTitle extends FormBuilderPageBase {

  /**
   * The session keys used to store the page title and body.
   *
   * @var array
   */
  const SESSION_KEYS = [
    'title' => 'form_builder:add_page:page_title',
    'body' => 'form_builder:add_page:page_body',
  ];

  /**
   * The field keys on the page paragraph.
   *
   * @var array
   */
  const FIELD_KEYS = [
    'title' => 'field_title',
    'body' => 'field_digital_form_body_text',
  ];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__page_title';
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
   * {@inheritdoc}
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state,
    $digitalForm = NULL,
    $stepParagraph = NULL,
    $pageParagraph = NULL,
  ) {
    $form = parent::buildForm($form, $form_state, $digitalForm, $stepParagraph, $pageParagraph);

    if (empty($stepParagraph)) {
      // If no page paragraph is passed in, this is "create" mode.
      $this->isCreate = TRUE;
      $defaultValues = [
        'title' => $this->session->get(self::SESSION_KEYS['title']),
        'body' => $this->session->get(self::SESSION_KEYS['body']),
      ];
    }
    else {
      // If a page paragraph is passed in, this is "edit" mode.
      $this->isCreate = FALSE;
      $defaultValues = [
        'title' => $this->getPageParagraphFieldValue(self::FIELD_KEYS['title']),
        'body' => $this->getPageParagraphFieldValue(self::FIELD_KEYS['body']),
      ];
    }

    $form['#theme'] = 'form__va_gov_form_builder__custom_question_page_title';

    // Page title.
    $form['field_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Step label'),
      '#required' => TRUE,
      '#default_value' => $defaultValues['title'],
    ];

    $form['field_digital_form_body_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Body content'),
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
    $title = $form_state->getValue(self::FIELD_KEYS['title']);
    $body = $form_state->getValue(self::FIELD_KEYS['body']);

    if ($this->isCreate) {
      $this->pageParagraph = $this->entityTypeManager->getStorage('paragraph')->create([
        'type' => 'digital_form_page',
        [self::FIELD_KEYS['title']] => $title,
        [self::FIELD_KEYS['body']] => $body,
      ]);
    }
    else {
      $this->pageParagraph->set(self::FIELD_KEYS['title'], $title);
      $this->pageParagraph->set(self::FIELD_KEYS['body'], $body);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($this->isCreate) {
      $this->session->set(
        self::SESSION_KEYS['title'],
        $form_state->getValue(self::FIELD_KEYS['title'])
      );
      $this->session->set(
        self::SESSION_KEYS['body'],
        $form_state->getValue(self::FIELD_KEYS['body'])
      );
    }
    else {
      parent::submitForm($form, $form_state);
    }

    $form_state->setRedirect('va_gov_form_builder.step.layout', [
      'nid' => $this->digitalForm->id(),
      'stepParagraphId' => $this->stepParagraph->id(),
    ]);
  }

}
