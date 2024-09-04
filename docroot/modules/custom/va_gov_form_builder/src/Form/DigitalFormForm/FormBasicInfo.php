<?php

namespace Drupal\va_gov_form_builder\Form\DigitalFormForm;

use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;

class FormBasicInfo extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'digital_form__add__form_basic_info';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Call the parent form to start with the default node form structure.
    //$form = parent::buildForm($form, $form_state);

    // $temp_store = \Drupal::service('tempstore.private')->get('va_gov_form_builder');
    // $temp_store->set('digital_form_in_progress', null);

    $form['#title'] = $this->t('Digital Form - Form Basic Info');

    $form['form_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form Name'),
      '#required' => TRUE,
    ];

    $form['form_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form Number'),
      '#required' => TRUE,
    ];

    $form['omb_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OMB Number'),
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    ];

    // Return the modified form.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    //parent::validateForm($form, $form_state);

    // Add custom validation logic.
    if (strlen($form_state->getValue('form_name')) < 5) {
      $form_state->setErrorByName('form_name', $this->t('The form name must be at least 5 characters long.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_name = $form_state->getValue('form_name');
    $form_number = $form_state->getValue('form_number');
    $omb_number = $form_state->getValue('omb_number');
    $default_paragraph_1 = Paragraph::create([
      'type' => 'digital_form_name_and_date_of_bi',
      'field_title' => 'Name w/ DOB',
      'field_include_date_of_birth' => true,
    ]);
    $default_paragraph_2 = Paragraph::create([
      'type' => 'digital_form_name_and_date_of_bi',
      'field_title' => 'Name w/out DOB',
      'field_include_date_of_birth' => false,
    ]);

    $digital_form = Node::create([
      'type' => 'digital_form',
      'title' => $form_name,
      'field_va_form_number' => $form_number,
      'field_omb_number' => $omb_number,
      'field_chapters' => [
        $default_paragraph_1,
        $default_paragraph_2,
      ],
    ]);

    $digital_form->save();

    // $temp_store = \Drupal::service('tempstore.private')->get('va_gov_form_builder');
    // $temp_store->set('digital_form_in_progress', $digital_form_in_progress);

    $form_state->setRedirect('va_gov_form_builder.digital_form.edit.add_step.yes_no', ['nid' => $digital_form->id()]);
  }
}
