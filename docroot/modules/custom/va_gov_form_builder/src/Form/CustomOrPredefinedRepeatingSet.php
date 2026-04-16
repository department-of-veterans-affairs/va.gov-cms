<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderStepCustomOrPredefinedBase;

/**
 * Form step for selecting a custom or predefined repeating-set step.
 */
class CustomOrPredefinedRepeatingSet extends FormBuilderStepCustomOrPredefinedBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__custom_or_predefined__repeating_set';
  }

  /**
   * {@inheritdoc}
   */
  protected function getButtonToStepTypeMapping() {
    return [
      'employment_history' => 'list_loop_employment_history',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $digitalForm = NULL, $stepParagraph = NULL) {
    $form = parent::buildForm($form, $form_state, $digitalForm, $stepParagraph);

    // Build the form.
    $form['#theme'] = 'form__va_gov_form_builder__custom_or_predefined__repeating_set';

    $form['predefined_options'] = [
      '#type' => 'container',
      'employment_history' => [
        '#type' => 'container',
        'label' => [
          '#markup' => $this->t('Employment history'),
        ],
        'description' => [
          '#markup' => '
            <p>This is a pre-defined multi-response pattern. No edits are available.</p>
            <p>Note: Selecting to add Employment history will update your step label to "Your employers".</p>
          ',
        ],
        'button' => [
          '#type' => 'submit',
          '#value' => $this->t('Add Employment history'),
          '#name' => 'employment_history',
          '#attributes' => [
            'class' => [
              'button',
              'button--primary',
              'form-submit',
            ],
          ],
          '#weight' => '20',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function setStepParagraphFromFormState(FormStateInterface $form_state) {
    // Predefined employment-history step always gets a hard-code
    // label "Your employers". This should override the label previously
    // entered by the user.
    // Note: the user is notified of this in the UI.
    $mapping = $this->getButtonToStepTypeMapping();
    if ($this->stepType === $mapping['employment_history']) {
      $this->stepLabel = 'Your employers';
    }

    parent::setStepParagraphFromFormState($form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $mapping = $this->getButtonToStepTypeMapping();
    if ($this->stepType === $mapping['employment_history']) {
      $form_state->setRedirect('va_gov_form_builder.step.home', [
        'nid' => $this->digitalForm->id(),
        'stepParagraphId' => $this->stepParagraph->id(),
      ]);
    }
    else {
      // Shouldn't happen, but catch-all for future.
      $form_state->setRedirect('va_gov_form_builder.layout', [
        'nid' => $this->digitalForm->id(),
      ]);
    }
  }

}
