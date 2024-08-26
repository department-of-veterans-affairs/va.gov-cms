<?php

namespace Drupal\va_gov_form_builder\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Form\FormStateInterface;

class DigitalFormController extends ControllerBase {

  public function wizard() {
    return $this->redirect('va_gov_form_builder.digital_form_form.form_name');
  }

  public function formName() {
    return $this->formBuilder()->getForm('Drupal\va_gov_form_builder\Form\DigitalFormForm\FormName');
  }

  public function formNumber() {
    return $this->formBuilder()->getForm('Drupal\va_gov_form_builder\Form\DigitalFormForm\FormNumber');
  }

  public function addStepYesNo() {
    return $this->formBuilder()->getForm('Drupal\va_gov_form_builder\Form\DigitalFormForm\AddStepYesNo');
  }

  public function addStepChooseType() {
    return $this->formBuilder()->getForm('Drupal\va_gov_form_builder\Form\DigitalFormForm\AddStepChooseType');
  }

  public function addStep($step_type) {
    //return $this->formBuilder()->getForm('Drupal\va_gov_form_builder\Form\DigitalFormForm\AddStep', $step_type);

    $paragraph = Paragraph::create([
      'type' => $step_type,
    ]);
    $paragraph_form = \Drupal::service('entity.form_builder')->getForm($paragraph);
    $paragraph_form['actions']['submit']['#value'] = $this->t('TEST');

    // Submission handled in hook_form_alter
    return $paragraph_form;
  }

  public function reviewAndSubmit() {
    return $this->formBuilder()->getForm('Drupal\va_gov_form_builder\Form\DigitalFormForm\ReviewAndSubmit');
  }

  public function addStepSubmitHandler(array &$form, FormStateInterface $form_state) {
    // Handle the form submission.
    xdebug_var_dump($form_state);
  }
}
