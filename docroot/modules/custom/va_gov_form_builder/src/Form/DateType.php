<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderBase;

/**
 * Form step for choosing the specific type of a date response.
 */
class DateType extends FormBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__date_type';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['#theme'] = 'form__va_gov_form_builder__date_type';

    $form['date_type'] = [
      '#type' => 'va_gov_form_builder__expanded_radios',
      '#title' => $this->t('Choose a date type for the submitter'),
      '#options' => [
        'single_date' => $this->t('Single date'),
        'date_range' => $this->t('Date range'),
      ],
      '#options_expanded_content' => [
        'single_date' => [
          '#theme' => 'page_element__va_gov_form_builder__expanded_radio__help_text_optional_image',
          '#help_text' => [
            '#type' => 'inline_template',
            '#template' => '<p>{{ description }} {{ link }}</p>',
            '#context' => [
              'description' => $this->t('Description of single date.'),
              'link' => [
                '#type' => 'link',
                '#title' => $this->t('VA Design System reference'),
                '#url' => Url::fromUri('https://design.va.gov/patterns/ask-users-for/dates')
                  ->setOptions([
                    'attributes' => [
                      'target' => '_blank',
                    ],
                  ]),
              ],
            ],
          ],
        ],
        'date_range' => [
          '#theme' => 'page_element__va_gov_form_builder__expanded_radio__help_text_optional_image',
          '#help_text' => [
            '#type' => 'inline_template',
            '#template' => '<p>{{ description }} {{ link }}</p>',
            '#context' => [
              'description' => $this->t('Description of date range.'),
              'link' => [
                '#type' => 'link',
                '#title' => $this->t('VA Design System reference'),
                '#url' => Url::fromUri('https://design.va.gov/patterns/ask-users-for/dates')
                  ->setOptions([
                    'attributes' => [
                      'target' => '_blank',
                    ],
                  ]),
              ],
            ],
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
    $dateType = $form_state->getValue('date_type');
    $redirectRoute = match ($dateType) {
      'single_date' => 'va_gov_form_builder.step.question.custom.date.single_date.page_title',
      'date_range' => 'va_gov_form_builder.step.question.custom.date.date_range.page_title',
      default => throw new \InvalidArgumentException('Invalid date type selected.'),
    };

    $form_state->setRedirect($redirectRoute, [
      'nid' => $nid,
      'stepParagraphId' => $stepParagraphId,
    ]);
  }

}
