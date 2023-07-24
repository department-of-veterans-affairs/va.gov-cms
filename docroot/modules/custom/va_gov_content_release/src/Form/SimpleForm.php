<?php

namespace Drupal\va_gov_content_release\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * The simple version of the form, as used on BRD.
 */
class SimpleForm extends BaseForm {

  /**
   * Build the form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['description'] = [
      '#prefix' => '<p>',
      '#markup' => $this->t('Any content you set to Published will automatically go live on VA.gov during the daily content release. However, if you need your content to go live sooner, you can perform a manual content release here.'),
      '#suffix' => '</p>',
      '#weight' => -10,
    ];
    $form['description_2'] = [
      '#prefix' => '<p>',
      '#markup' => $this->t('Content release can take up to 30 minutes to finish.'),
      '#suffix' => '</p>',
      '#weight' => -10,
    ];

    $form['confirm'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I understand that all VA content set to Published will go live once the release is finished.'),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Release content'),
      '#button_type' => 'primary',
      '#states' => [
        'disabled' => [':input[name="confirm"]' => ['checked' => FALSE]],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('confirm') !== 1) {
      $form_state->setErrorByName('confirm', $this->t('You must confirm that you understand this implication.'));
    }
  }

}
