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

    $imageDir = '/modules/custom/va_gov_form_builder/images/';
    $form['response_kind'] = [
      '#type' => 'radios',
      '#title' => $this->t('What kind of response will the submitter provide?'),
      '#options' => [
        'choice' => $this->t('Choice'),
        'date' => $this->t('Date'),
        'text' => $this->t('Text and/or numbers'),
      ],
      '#option_attributes' => [
        'choice' => [
          'class' => ['va-gov-form-builder__response-kind-choice'],
        ],
        'date' => [
          'class' => ['va-gov-form-builder__response-kind-date'],
        ],
        'text' => [
          'class' => ['va-gov-form-builder__response-kind-text'],
        ],
      ],
      '#options_data' => [
        'choice' => [
          'help_text' => $this->t('This allows one or multiple items to be selected from a provided list.'),
          'image' => [
            'alt_text' => 'Example of a list of choices',
            'caption' => 'Example of a list of choices',
            'url' => $imageDir . 'response-kind-choice.png',
          ],
        ],
        'date' => [
          'help_text' => $this->t('This allows a specific or approximate date or date range to be input.'),
          'image' => [
            'alt_text' => 'Example of a date range',
            'caption' => 'Example of a date range',
            'url' => $imageDir . 'response-kind-date.png',
          ],
        ],
        'text' => [
          'help_text' => $this->t('This allows the submitter to fill in the blank for the question you are asking.'),
          'image' => [
            'alt_text' => 'Example of input fields',
            'caption' => 'Example of input fields',
            'url' => $imageDir . 'response-kind-text.png',
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
    xdebug_var_dump($form_state->getValues());
    exit;
  }

}
