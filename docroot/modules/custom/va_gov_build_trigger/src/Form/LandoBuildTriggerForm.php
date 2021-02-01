<?php

namespace Drupal\va_gov_build_trigger\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements build trigger form overrides for the Lando environment.
 */
class LandoBuildTriggerForm extends TugboatBuildTriggerForm {

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

    $form['help_1']['#markup'] = $this->t('Release content to update the front end of this local environment with the latest published content changes.');
    $form['section_1']['selection']['#options']['default'] = $this->t('Use default - the frontend version from the time this local environment was created.');

    return $form;
  }

}
