<?php

namespace Drupal\va_gov_workflow_assignments\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class WorkflowAssignmentsAdminForm.
 */
class WorkflowAssignmentsAdminForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'workflow_assignments_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('workflow_assignments_admin.settings');

    $form['types_to_exclude'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Content types that are not on va.gov.'),
      '#description' => $this->t('Machine names of content types, one per line, that are not available to va.gov'),
      '#weight' => '10',
      '#default_value' => $config->get('types_to_exclude'),
      '#rows' => 10,
      '#cols' => 60,
      '#resizeable' => 'both',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#weight' => '20',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('workflow_assignments_admin.settings');
    $config
      ->set('types_to_exclude', $this->convertSort($form_state->getValue('types_to_exclude')))
      ->save();
  }

  /**
   * Dedupe, sort and convert to one per line.
   *
   * @param string $multivalues_raw
   *   A comma or new line separated string of multiple values.
   *
   * @return string
   *   A new-line separated string that has been deduped and sorted.
   */
  private function convertSort($multivalues_raw) {
    // Clean up any bad data by converting all separators into ','.
    $multivalues = str_replace(["\r\n", "\n", "\r", ', ', ' ,', " "], ',', strtolower($multivalues_raw));
    // Store the types array as property.
    $values = explode(',', $multivalues);
    $values = array_unique($values);
    natcasesort($values);
    $multivalues_sorted = implode("\n", $values);
    return $multivalues_sorted;
  }

}
