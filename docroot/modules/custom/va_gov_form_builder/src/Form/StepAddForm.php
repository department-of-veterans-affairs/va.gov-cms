<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Class StepAddForm.
 *
 * @package Drupal\form_builder\Form
 */
class StepAddForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'step_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $paragraph_type = NULL) {
    // Handle cases where no paragraph type is provided.
    if (!$paragraph_type) {
      $form['error'] = [
        '#markup' => $this->t('No paragraph type provided.'),
      ];
      return $form;
    }

    // Load the paragraph type entity.
    $paragraph_type_entity = ParagraphsType::load($paragraph_type);
    if (!$paragraph_type_entity) {
      // Handle the case where the paragraph type does not exist.
      $form['error'] = [
        '#markup' => $this->t('The paragraph type does not exist.'),
      ];
      return $form;
    }

    // Create a new paragraph entity of the chosen type.
    $paragraph = Paragraph::create(['type' => $paragraph_type]);

    // Build the form for the new paragraph entity.
    $paragraph_form = \Drupal::service('entity.form_builder')
      ->getForm($paragraph, 'default');

    if ($paragraph_type == 'digital_form_name_and_date_of_bi') {
      $form['paragraph_form']['field_title'] = $paragraph_form['field_title'];
      $form['paragraph_form']['field_include_date_of_birth'] = $paragraph_form['field_include_date_of_birth'];
    }

    // Add a submit button for your custom form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Step'),
    ];

    //\Drupal::messenger()->addMessage('<pre>' . print_r($form['field_include_date_of_birth'], true) . '</pre>');
    //\Drupal::messenger()->addMessage(print_r(array_keys($form['field_include_date_of_birth']['widget'])));
    //\Drupal::messenger()->addMessage(print_r($paragraph->getFields()['field_title']));

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Add validation if needed.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Do nothing here; actual submission logic is handled in the controller.
  }
}
