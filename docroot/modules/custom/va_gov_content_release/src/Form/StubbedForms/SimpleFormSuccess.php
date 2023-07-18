<?php

namespace Drupal\va_gov_content_release\Form\StubbedForms;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_content_release\Form\SimpleForm;

/**
 * A stubbed version of the SimpleForm class.
 *
 * Submissions of this form will always succeed.
 */
class SimpleFormSuccess extends SimpleForm {

  /**
   * Submit the build trigger form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->reporter->reportInfo($this->t('Content release requested successfully.'));
  }

}
