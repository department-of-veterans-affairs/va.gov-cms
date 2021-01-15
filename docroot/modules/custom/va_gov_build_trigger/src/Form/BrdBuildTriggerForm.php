<?php

namespace Drupal\va_gov_build_trigger\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements build trigger form overrides for the BRD environment.
 */
class BrdBuildTriggerForm extends BuildTriggerForm {

  /**
   * Build the build trigger form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $description = $this->t('A content release for this environment will be handled by VFS Jenkins.');
    $form['environment_target']['#description'] = $description;

    return $form;
  }

}
