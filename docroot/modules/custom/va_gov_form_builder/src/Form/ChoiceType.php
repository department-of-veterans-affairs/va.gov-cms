<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
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
    $form['#theme'] = 'form__va_gov_form_builder__choice_type';

    $form['choice_type'] = [
      '#type' => 'va_gov_form_builder__expanded_radios',
      '#title' => $this->t('What style of choice fits the question you will ask?'),
      '#options' => [
        'radio' => $this->t('Radio'),
        'checkbox' => $this->t('Checkbox'),
      ],
//      '#options_expanded_content' => [
//        'radio' => [
//          '#theme' => 'page_element__va_gov_form_builder__expanded_radio__help_text_optional_image',
//          '#help_text' => [
//            '#type' => 'inline_template',
//            '#template' => '<p>{{ description }} {{ link }}</p>',
//            '#context' => [
//              'description' => $this->t('We use radio buttons to enable this option.'),
//              'link' => [
//                '#type' => 'link',
//                '#title' => $this->t('VA Design System reference'),
//                '#url' => Url::fromUri('https://design.va.gov/patterns/ask-users-for/dates')
//                  ->setOptions([
//                    'attributes' => [
//                      'target' => '_blank',
//                    ],
//                  ]),
//              ],
//            ],
//          ],
//        ],
//        'checkbox' => [
//          '#theme' => 'page_element__va_gov_form_builder__expanded_radio__help_text_optional_image',
//          '#help_text' => [
//            '#type' => 'inline_template',
//            '#template' => '<p>{{ description }} {{ link }}</p>',
//            '#context' => [
//              'description' => $this->t('Description of date range.'),
//              'link' => [
//                '#type' => 'link',
//                '#title' => $this->t('VA Design System reference'),
//                '#url' => Url::fromUri('https://design.va.gov/patterns/ask-users-for/dates')
//                  ->setOptions([
//                    'attributes' => [
//                      'target' => '_blank',
//                    ],
//                  ]),
//              ],
//            ],
//          ],
//        ],
//      ],
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
