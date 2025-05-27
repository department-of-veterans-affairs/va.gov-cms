<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderBase;

/**
 * Form step for choosing the specific type of choice response.
 */
class ChoiceType extends FormBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__choice_type';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['#theme'] = 'form__va_gov_form_builder__choice_type';

    $form['choice_type'] = [
      '#type' => 'va_gov_form_builder__expanded_radios',
      '#title' => $this->t('Decide between a single choice or multiple choices that the submitter will indicate.'),
      '#options' => [
        'radio' => $this->t('Only one choice is allowed for the question from the list.'),
        'checkbox' => $this->t('More than once choice can be selected.'),
      ],
      '#options_expanded_content' => [
        'radio' => [
          '#theme' => 'page_element__va_gov_form_builder__expanded_radio__help_text_optional_image',
          '#help_text' => [
            '#type' => 'inline_template',
            '#template' => '<p>{{ description }}</p>',
            '#context' => [
              'description' => $this->t('We use radio buttons to enable this option.'),
            ],
          ],
          '#image' => [
            'url' => self::IMAGE_DIR . 'radio-example.png',
            'alt' => $this->t('A radio option list example.'),
            'caption' => $this->t('Example of a radio button list'),
          ],
        ],
        'checkbox' => [
          '#theme' => 'page_element__va_gov_form_builder__expanded_radio__help_text_optional_image',
          '#help_text' => [
            '#type' => 'inline_template',
            '#template' => '<p>{{ description }}</p>',
            '#context' => [
              'description' => $this->t('Checkbox lists are the elements we use for this.'),
            ],
          ],
          '#image' => [
            'url' => self::IMAGE_DIR . 'checkbox-example.png',
            'alt' => $this->t('Example of a checkbox list'),
            'caption' => $this->t('Example of a checkbox list'),
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
    $choiceType = $form_state->getValue('choice_type');
    $redirectRoute = match ($choiceType) {
      'radio' => 'va_gov_form_builder.step.question.custom.choice.radio.page_title',
      'checkbox' => 'va_gov_form_builder.step.question.custom.choice.checkbox.page_title',
      default => throw new \InvalidArgumentException('Invalid option selected.'),
    };

    $form_state->setRedirect($redirectRoute, [
      'nid' => $nid,
      'stepParagraphId' => $stepParagraphId,
    ]);
  }

}
