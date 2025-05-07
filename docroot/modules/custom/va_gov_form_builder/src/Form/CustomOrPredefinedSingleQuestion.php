<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderStepCustomOrPredefinedBase;

/**
 * Form step for selecting a custom or predefined single-question step.
 */
class CustomOrPredefinedSingleQuestion extends FormBuilderStepCustomOrPredefinedBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__custom_or_predefined_single_question';
  }

  /**
   * {@inheritdoc}
   */
  protected function getButtonToStepTypeMapping() {
    return [
      'customize' => 'digital_form_custom_step',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $digitalForm = NULL, $stepParagraph = NULL) {
    $form = parent::buildForm($form, $form_state, $digitalForm, $stepParagraph);

    // Build the form.
    $form['#theme'] = 'form__va_gov_form_builder__custom_or_predefined__single_question';

    $form['actions']['customize'] = [
      '#type' => 'submit',
      '#value' => $this->t('Customize'),
      '#name' => 'customize',
      '#attributes' => [
        'class' => [
          'button',
          'button--primary',
          'form-submit',
        ],
      ],
      '#weight' => '20',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $mapping = $this->getButtonToStepTypeMapping();

    if ($this->stepType === $mapping['customize']) {
      $form_state->setRedirect('va_gov_form_builder.step.question.custom.kind', [
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
