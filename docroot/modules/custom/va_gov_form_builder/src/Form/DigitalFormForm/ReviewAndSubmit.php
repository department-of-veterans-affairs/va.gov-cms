<?php

namespace Drupal\va_gov_form_builder\Form\DigitalFormForm;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ReviewAndSubmit extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'digital_form_form__review_and_submit';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $step_type = NULL) {
    $temp_store = \Drupal::service('tempstore.private')->get('va_gov_form_builder');
    $digital_form_in_progress = $temp_store->get('digital_form_in_progress');
    //xdebug_var_dump($digital_form_in_progress->get('field_chapters')->getValue()[0]['entity']->get('field_include_date_of_birth')->value);

    $form['#title'] = $this->t('Digital Form - Review and Submit');

    $form['form_review'] = [
      '#type' => 'container',
    ];

    $form['form_review']['form_name'] = [
      '#markup' => '<div><strong>Form Name:</strong> ' . $digital_form_in_progress->get('title')->value . '</div>',
    ];

    $form['form_review']['form_number'] = [
      '#markup' => '<div><strong>Form Number:</strong> ' . $digital_form_in_progress->get('field_va_form_number')->value . '</div>',
    ];

    $form['form_review']['steps'] = [
      '#type' => 'container',
    ];

    foreach($digital_form_in_progress->get('field_chapters')->getValue() as $step) {
      //xdebug_var_dump($step);
      $form['form_review']['steps'][] = [
        'steps_header' => [
          '#markup' => '<h2>Step</h2>',
        ],
        'title' => [
          '#markup' => '<div><strong>Chapter Title:</strong> ' . $step['entity']->get('field_title')->value . '</div>',
        ],
        'field_include_date_of_birth' => [
          '#markup' => '<div><strong>Include DOB:</strong> ' . $step['entity']->get('field_include_date_of_birth')->value . '</div>',
        ],
      ];
    }

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Form'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $temp_store = \Drupal::service('tempstore.private')->get('va_gov_form_builder');
    $digital_form_in_progress = $temp_store->get('digital_form_in_progress');
    $steps = $digital_form_in_progress->get('field_chapters')->getValue();

    $new_paragraph = Paragraph::create([
      'type' => $this->step_type,
    ]);

    foreach($this->step_fields as $step_field) {
      $new_paragraph->set($step_field, $form_state->getValue($step_field));
    }
    $steps[] = $new_paragraph;
    $digital_form_in_progress->set('field_chapters', $steps);
    $temp_store->set('digital_form_in_progress', $digital_form_in_progress);

    \Drupal::messenger()->addMessage($this->t('Step added'));
    $form_state->setRedirect('va_gov_form_builder.digital_form_form.add_step.yes_no');
  }
}
