<?php

namespace Drupal\va_gov_build_trigger\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements build trigger form.
 */
class BuildTriggerForm extends FormBase {

  /**
   * Build the build trigger form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Rebuild site'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller.  it must
   * be unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'va_gov_build_trigger_build_trigger_form';
  }

  /**
   * Submit the build trigger form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Placeholder until change to Jenkins url.
    $uri = 'http://example.com';

    try {
      $response = \Drupal::httpClient()->get($uri, ['headers' => ['Accept' => 'text/plain']]);
      $data = (string) $response->getBody();
      if (empty($data)) {
        return FALSE;
      }

      // Get our sql formatted date.
      $time_raw = format_date(time(), 'html_datetime');
      $time = strtok($time_raw, '+');

      // We only need to update field table - field is set on node import.
      $query = \Drupal::database()
        ->update('node__field_page_last_built')
        ->fields(['field_page_last_built_value' => $time]);
      $query->execute();

      // We only need to update - revision field is set on node import.
      $query_revision = \Drupal::database()
        ->update('node_revision__field_page_last_built')
        ->fields(['field_page_last_built_value' => $time]);
      $query_revision->execute();

      \Drupal::messenger()->addMessage(t('Site rebuild request has been sent to :uri.', [':uri' => $uri]), 'status');

    }
    catch (RequestException $e) {
      return FALSE;
    }

  }

}
