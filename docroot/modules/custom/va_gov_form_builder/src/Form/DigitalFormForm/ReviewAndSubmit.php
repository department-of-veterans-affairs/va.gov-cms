<?php

namespace Drupal\va_gov_form_builder\Form\DigitalFormForm;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

class ReviewAndSubmit extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'digital_form_form__review_and_submit';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    $form['#title'] = $this->t('Digital Form - Review and Submit');

    $form['nid'] = [
      '#type' => 'hidden',
      '#value' => $node->id(),
    ];

    $form['form_review'] = [
      '#type' => 'container',
    ];

    $form['form_review']['form_name'] = [
      '#markup' => '<h2><strong>Form Name:</strong> ' . $node->get('title')->value . '</h2>',
    ];

    $form['form_review']['form_number'] = [
      '#markup' => '<h2><strong>Form Number:</strong> ' . $node->get('field_va_form_number')->value . '</h2>',
    ];

    $form['form_review']['steps'] = [
      '#type' => 'container',
    ];

    foreach($node->get('field_chapters')->referencedEntities() as $step) {
      $form['form_review']['steps'][] = [
        'steps_header' => [
          '#markup' => '<h4>Step</h4>',
        ],
        'title' => [
          '#markup' => '<div><strong>Chapter Title:</strong> ' . $step->get('field_title')->value . '</div>',
        ],
        'field_include_date_of_birth' => [
          '#markup' => '<div><strong>Include DOB:</strong> ' . $step->get('field_include_date_of_birth')->value . '</div>',
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
    // $temp_store = \Drupal::service('tempstore.private')->get('va_gov_form_builder');
    // $digital_form_in_progress = $temp_store->get('digital_form_in_progress');
    // $steps = $digital_form_in_progress->get('field_chapters')->getValue();

    //$form_state->setRedirect('va_gov_form_builder.digital_form_form.add_step.yes_no');

    $nid = $form_state->getValue('nid');

    $url = Url::fromRoute('entity.node.edit_form', ['node' => $nid]);
    $form_state->setRedirectUrl($url);
  }
}
