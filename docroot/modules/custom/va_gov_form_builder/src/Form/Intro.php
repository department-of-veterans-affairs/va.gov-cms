<?php

namespace Drupal\va_gov_form_builder\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderBase;

/**
 * Form step for creating a new form (new conversion).
 */
class Intro extends FormBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_builder__intro';
  }

  /**
   * {@inheritdoc}
   */
  protected function getFields() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['working_with_form_builder_header'] = [
      '#type' => 'html_tag',
      '#tag' => 'h2',
      '#children' => $this->t('Working with the Form Builder'),
    ];

    $form['paragraph_1'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#children' => $this->t('This is where the conversion of an existing form, or the continued editing
        of a converted form, takes place.'),
    ];

    $form['before_beginning_header'] = [
      '#type' => 'html_tag',
      '#tag' => 'h3',
      '#children' => $this->t('Before beginning'),
    ];

    $form['paragraph_2'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#children' => $this->t('Make sure you have a copy of the form you are intending to convert.
        This will make it easier to reference the information
        that will be needed, and the order in which it appears for your users.'),
    ];

    $form['paragraph_3'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#children' => $this->t('When returning to update an existing conversion, your past projects
        are listed under <b>Projects</b> at right.'),
    ];

    $form['paragraph_4'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#children' => $this->t('If you need to access a conversion that you did not create, you can use the
      <b>content search</b> to find that project.'),
    ];

    $form['paragraph_5'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#children' => $this->t('To begin a new project select <b>Start conversion</b>.'),
    ];

    $form['actions']['start_conversion'] = [
      '#type' => 'submit',
      '#value' => $this->t('Start conversion'),
      '#attributes' => [
        'class' => [
          'button',
          'button--primary',
          'js-form-submit',
          'form-submit',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('va_gov_form_builder.start_conversion');
  }

}
