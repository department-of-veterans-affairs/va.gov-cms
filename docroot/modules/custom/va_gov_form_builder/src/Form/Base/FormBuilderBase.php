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
   *
   * After initially containing some logic, this function
   * is now empty, and this entire class is a candiate
   * for removal. Leaving it here for now, as it might prove
   * necessary as we continue on.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
  }

}
