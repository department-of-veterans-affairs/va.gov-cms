<?php

namespace Drupal\va_gov_form_builder\Form\DigitalFormForm;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;

class AddStepYesNo extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'digital_form__edit__add_step';
  }

  private static function buildStepSummary($paragraph) {
    if ($paragraph->bundle() == 'digital_form_name_and_date_of_bi') {
      return [
        'steps_header' => [
          '#markup' => '<h3>Step</h3>',
        ],
        'title' => [
          '#markup' => '<div><strong>Chapter Title:</strong> ' . $paragraph->get('field_title')->value . '</div>',
        ],
        'field_include_date_of_birth' => [
          '#markup' => '<div><strong>Include DOB:</strong> ' . $paragraph->get('field_include_date_of_birth')->value . '</div>',
        ],
      ];
    }

    return null;
  }

  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    // $temp_store = \Drupal::service('tempstore.private')->get('va_gov_form_builder');
    // $digital_form_in_progress = $temp_store->get('digital_form_in_progress');

    $form['#title'] = $this->t('Digital Form - Add Step');

    $form['nid'] = [
      '#type' => 'hidden',
      '#value' => $node->id(),
    ];

    $form['steps_container'] = [
      '#type' => 'container',
    ];
    foreach($node->get('field_chapters')->referencedEntities() as $step) {
      $step_form = $this->buildStepSummary($step);

      if ($step_form) {
        $form['steps_container']['steps'][] = $step_form;
      }
    }
    if (count($form['steps_container']['steps']) > 0) {
      $form['steps_container']['#attributes'] = [
        'style' => 'padding: 10px; border: 1px solid #000;',
      ];
    }

    $form['add_more_steps_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'style' => 'padding-top: 50px;',
      ],
      'add_more_steps' => [
        '#title' => $this->t('Do you have any steps to add?'),
        '#type' => 'radios',
        '#options' => [
          'yes' => $this->t('Yes'),
          'no' => $this->t('No'),
        ],
        '#required' => TRUE,
      ],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $add_more_steps = $form_state->getValue('add_more_steps');
    $nid = $form_state->getValue('nid');

    if ($add_more_steps === 'yes') {
      $form_state->setRedirect('va_gov_form_builder.digital_form.edit.add_step.choose_type', ['nid' => $nid]);
    } else {
      $form_state->setRedirect('va_gov_form_builder.digital_form.edit.review_and_submit', ['nid' => $nid]);
    }
  }
}
