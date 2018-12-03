<?php

namespace Drupal\json_content\Form;

use Drupal\user\Form\UserLoginForm as BaseUserLoginForm;
use Drupal\Core\Form\FormStateInterface;

class UserLoginForm extends BaseUserLoginForm {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $form_state->setRedirect('<front>');
  }

}