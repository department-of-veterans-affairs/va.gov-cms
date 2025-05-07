<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderBase;

/**
 * Form step for choosing a question's response kind.
 */
class ResponseKind extends FormBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__response_kind';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#theme'] = 'form__va_gov_form_builder__response_kind';

    $form['response_kind'] = [
      '#type' => 'va_gov_form_builder__expanded_radios',
      '#title' => $this->t('What kind of response will the submitter provide?'),
      '#options' => [
        'choice' => $this->t('Choice'),
        'date' => $this->t('Date'),
        'text' => $this->t('Text and/or numbers'),
      ],
      '#options_expanded_content' => [
        'choice' => [
          '#theme' => 'page_element__va_gov_form_builder__expanded_radio__help_text_optional_image',
          '#help_text' => $this->t('This allows one or multiple items to be selected from a provided list.'),
          '#image' => [
            'alt_text' => 'Example of a list of choices',
            'caption' => 'Example of a list of choices',
            'url' => self::IMAGE_DIR . 'response-kind-choice.png',
          ],
        ],
        'date' => [
          '#theme' => 'page_element__va_gov_form_builder__expanded_radio__help_text_optional_image',
          '#help_text' => $this->t('This allows a specific or approximate date or date range to be input.'),
          '#image' => [
            'alt_text' => 'Example of a date range',
            'caption' => 'Example of a date range',
            'url' => self::IMAGE_DIR . 'response-kind-date.png',
          ],
        ],
        'text' => [
          '#theme' => 'page_element__va_gov_form_builder__expanded_radio__help_text_optional_image',
          '#help_text' => $this->t('This allows the submitter to fill in the blank for the question you are asking.'),
          '#image' => [
            'alt_text' => 'Example of input fields',
            'caption' => 'Example of input fields',
            'url' => self::IMAGE_DIR . 'response-kind-text.png',
          ],
        ],
      ],
      '#required' => TRUE,
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $routeParameters = \Drupal::routeMatch()->getParameters()->all();
    $nid = $routeParameters['nid'];
    $stepParagraphId = $routeParameters['stepParagraphId'];

    $responseKind = $form_state->getValue('response_kind');

    switch ($responseKind) {
      case 'date':
        $form_state->setRedirect('va_gov_form_builder.step.question.custom.date.type', [
          'nid' => $nid,
          'stepParagraphId' => $stepParagraphId,
        ]);
        break;

      case 'choice':
        $form_state->setRedirect('va_gov_form_builder.step.question.custom.choice.type', [
          'nid' => $nid,
          'stepParagraphId' => $stepParagraphId,
        ]);
        break;

      case 'text':
        $form_state->setRedirect('va_gov_form_builder.step.question.custom.text.type', [
          'nid' => $nid,
          'stepParagraphId' => $stepParagraphId,
        ]);
        break;
    }
  }

}
