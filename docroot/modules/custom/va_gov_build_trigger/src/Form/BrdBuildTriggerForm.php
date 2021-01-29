<?php

namespace Drupal\va_gov_build_trigger\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements build trigger form for the BRD environment.
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

    $form['#attached']['library'][] = 'va_gov_build_trigger/build_trigger_form';

    $form['help_1'] = [
      '#prefix' => '<p>',
      '#markup' => $this->t('Any content you set to Published will automatically go live on VA.gov during the daily content release. However, if you need your content to go live sooner, you can perform a manual content release here.'),
      '#suffix' => '</p>',
      '#weight' => -10,
    ];
    $form['help_2'] = [
      '#prefix' => '<p>',
      '#markup' => $this->t('Content release can take up to 15 minutes to finish.'),
      '#suffix' => '</p>',
      '#weight' => -10,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['confirm'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I understand that all VA content set to Published will go live once the release is finished.'),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Release content'),
      '#button_type' => 'primary',
      '#states' => [
        'disabled' => [':input[name="confirm"]' => ['checked' => FALSE]],
      ],
    ];

    if ($this->webBuildStatus->getWebBuildStatus()) {
      // A build is pending, so set a display.
      $form['tip']['#prefix'] = '<em>';
      $form['tip']['#markup'] = $this->t('A content release has been queued.');
      $form['tip']['#suffix'] = '</em>';
      $form['tip']['#weight'] = 100;
    }

    return $form;
  }

}
