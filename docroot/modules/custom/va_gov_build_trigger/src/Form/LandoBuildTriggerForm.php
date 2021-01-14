<?php

namespace Drupal\va_gov_build_trigger\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements build trigger form overrides for the Lando environment.
 */
class LandoBuildTriggerForm extends BuildTriggerForm {

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

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced Options'),
      '#weight' => 10,
    ];

    $form['advanced']['front_end_branch'] = [
      '#type' => 'textfield',
      '#description' => $this->t('Enter text to search Front-end branches and open PRs.'),
      '#autocomplete_route_name' => 'va_gov_build_trigger.front_end_branches_autocomplete',
      '#autocomplete_route_parameters' => [
        'count' => 10,
      ],
      '#maxlength' => 1024,
    ];

    if ($this->webBuildStatus->useContentExport()) {
      $form['advanced']['full_rebuild'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Trigger a full Content Export Rebuild.'),
      ];
    }

    $description = $this->t('Content releases within Lando. You may press this button to trigger a content release. Please note this could take several minutes to run.');
    $form['environment_target']['#description'] = $description;

    return $form;
  }

  /**
   * Submit the build trigger form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $front_end_branch = $form_state->getValue('front_end_branch');
    $full_rebuild = (bool) $form_state->getValue('full_rebuild');
    $git_ref = NULL;
    if ($front_end_branch && preg_match("/.+\\s\\(([^\\)]+)\\)/", $front_end_branch, $matches)) {
      $git_ref = $matches[1];
    }

    $this->buildFrontend->triggerFrontendBuild($git_ref, $full_rebuild);
  }

}
