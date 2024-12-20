<?php

namespace Drupal\va_gov_form_builder\Form\Base;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Abstract base class for Form Builder form steps.
 */
abstract class FormBuilderBase extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Add styles.
    $form['#attached']['library'][] = 'va_gov_form_builder/va_gov_form_builder_styles';

    return $form;
  }

}
