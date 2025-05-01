<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderBase;

/**
 * Form step for choosing the specific type of a text response.
 */
class TextType extends FormBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__text_type';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#theme'] = 'form__va_gov_form_builder__text_type';

    $form['text_type'] = [
      '#type' => 'va_gov_form_builder__expanded_radios',
      '#title' => $this->t('Choose a text type for the submitter'),
      '#options' => [
        'text' => $this->t('Text or numerical input field'),
        'textarea' => $this->t('Text area'),
      ],
      '#options_expanded_content' => [
        'text' => [
          '#theme' => 'page_element__va_gov_form_builder__expanded_radio__help_text_optional_image',
          '#help_text' => [
            '#type' => 'inline_template',
            '#template' => '<p>{{ description }}</p>',
            '#context' => [
              'description' => $this->t('Note these fields do not provide validation.'),
            ],
          ],
          '#image' => [
            'alt_text' => 'Example of text input fields',
            'caption' => 'Example of text input fields',
            'url' => self::IMAGE_DIR . 'response-kind-text.png',
          ],
        ],
        'textarea' => [
          '#theme' => 'page_element__va_gov_form_builder__expanded_radio__help_text_optional_image',
          '#help_text' => [
            '#type' => 'inline_template',
            '#template' => '<p>{{ description }}</p>',
            '#context' => [
              'description' => $this->t('Use this version if you need to provide a larger input space for the submitter.'),
            ],
          ],
          '#image' => [
            'alt_text' => 'Example of textarea field',
            'caption' => 'Example of textarea field',
            'url' => self::IMAGE_DIR . 'response-kind-textarea.png',
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
    $textType = $form_state->getValue('text_type');
    $redirectRoute = match ($textType) {
      'text' => 'va_gov_form_builder.step.question.custom.text.text_input.page_title',
      'textarea' => 'va_gov_form_builder.step.question.custom.text.text_area.page_title',
      default => throw new \InvalidArgumentException('Invalid text type selected.'),
    };

    $form_state->setRedirect($redirectRoute, [
      'nid' => $nid,
      'stepParagraphId' => $stepParagraphId,
    ]);
  }

}
