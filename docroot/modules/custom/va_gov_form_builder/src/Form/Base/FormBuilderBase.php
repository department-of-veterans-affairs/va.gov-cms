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
    $form['#theme'] = 'va_gov_form_builder';
    $form['#title'] = $this->t('Form Builder');

    // Add styles.
    $form['#attached']['html_head'][] = [
      [
        '#tag' => 'link',
        '#attributes' => [
          'rel' => 'stylesheet',
          'href' => 'https://unpkg.com/@department-of-veterans-affairs/css-library@0.16.0/dist/tokens/css/variables.css',
        ],
      ],
      'external_stylesheet',
    ];
    $form['#attached']['library'][] = 'va_gov_form_builder/va_gov_form_builder_styles';

    return $form;
  }

}
