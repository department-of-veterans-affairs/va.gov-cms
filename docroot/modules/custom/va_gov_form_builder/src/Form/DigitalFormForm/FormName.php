<?php

namespace Drupal\va_gov_form_builder\Form\DigitalFormForm;

use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class FormName extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'digital_form_form__form_name';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Call the parent form to start with the default node form structure.
    //$form = parent::buildForm($form, $form_state);

    $temp_store = \Drupal::service('tempstore.private')->get('va_gov_form_builder');
    $temp_store->set('digital_form_in_progress', null);

    $form['#title'] = $this->t('Digital Form - Form Name');

    $form['form_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Form Name'),
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
    // Custom submit handler, or call the parent to use the default behavior.
    //parent::submitForm($form, $form_state);

    // Additional custom submission logic.
    // \Drupal::messenger()->addMessage($this->t('The custom form has been submitted.'));

    $digital_form_in_progress = Node::create([
      'type' => 'digital_form',
      'title' => $form_state->getValue('form_name'),
    ]);

    $temp_store = \Drupal::service('tempstore.private')->get('va_gov_form_builder');
    $temp_store->set('digital_form_in_progress', $digital_form_in_progress);
    $form_state->setRedirect('va_gov_form_builder.digital_form_form.form_number');
  }
}
