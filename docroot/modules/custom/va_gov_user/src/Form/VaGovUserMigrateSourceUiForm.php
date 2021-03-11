<?php

namespace Drupal\va_gov_user\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\migrate_source_ui\Form\MigrateSourceUiForm;

/**
 * Contribute form.
 */
class VaGovUserMigrateSourceUiForm extends MigrateSourceUiForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $options = [];
    foreach ($form['migrations']['#options'] as $id => $option) {
      if ($id === 'user_import') {
        $options[$id] = $option;
      }
    }
    $form['migrations']['#options'] = $options;

    return $form;
  }

}
