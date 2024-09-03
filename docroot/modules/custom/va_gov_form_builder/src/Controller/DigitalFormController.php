<?php

namespace Drupal\va_gov_form_builder\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormStateInterface;

class DigitalFormController extends ControllerBase {

  public function wizard() {
    return $this->redirect('va_gov_form_builder.digital_form.add.form_basic_info');
  }

  public function formBasicInfo() {
    return $this->formBuilder()->getForm('Drupal\va_gov_form_builder\Form\DigitalFormForm\FormBasicInfo');
  }

  public function formNumber() {
    return $this->formBuilder()->getForm('Drupal\va_gov_form_builder\Form\DigitalFormForm\FormNumber');
  }

  public function addStepYesNo($nid) {
    $node = Node::load($nid);

    if ($node) {
      return $this->formBuilder()->getForm('Drupal\va_gov_form_builder\Form\DigitalFormForm\AddStepYesNo', $node);
    } else {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
  }

  public function addStepChooseType($nid) {
    $node = Node::load($nid);

    if ($node) {
      return $this->formBuilder()->getForm('Drupal\va_gov_form_builder\Form\DigitalFormForm\AddStepChooseType', $node);
    } else {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
  }

  public function addStep($nid, $step_type) {
    $node = Node::load($nid);

    if ($node) {
      return $this->formBuilder()->getForm('Drupal\va_gov_form_builder\Form\DigitalFormForm\AddStep', $node, $step_type);
    } else {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    // $paragraph = Paragraph::create([
    //   'type' => $step_type,
    // ]);
    // $paragraph_form = \Drupal::service('entity.form_builder')->getForm($paragraph);
    // $paragraph_form['actions']['submit']['#value'] = $this->t('TEST');

    // // Submission handled in hook_form_alter
    // return $paragraph_form;
  }

  public function reviewAndSubmit($nid) {
    $node = Node::load($nid);

    if ($node) {
      return $this->formBuilder()->getForm('Drupal\va_gov_form_builder\Form\DigitalFormForm\ReviewAndSubmit', $node);
    } else {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
  }
}
