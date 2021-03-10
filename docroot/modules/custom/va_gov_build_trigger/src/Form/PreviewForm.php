<?php

namespace Drupal\va_gov_build_trigger\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\node\NodeInterface;

/**
 * Implements build trigger form.
 */
class PreviewForm extends FormBase {

  /**
   * Build the build trigger form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'button',
      '#value' => $this->t('Preview'),
      '#button_type' => 'primary',
      '#ajax' => [
        'callback' => '::submitForm',
      ],
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
    return 'va_gov_build_trigger_preview_form';
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
    $host = \Drupal::request()->getHost();
    $nid = '';
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface) {
      $nid = $node->id();
    }

    return self::createModal($host, $nid);
  }

  /**
   * Builds a modal for preview page.
   */
  public function createModal($host, $nid) {
    $preview_response = new AjaxResponse();
    $host = \Drupal::request()->getHost();
    $title = 'You are previewing ' . $host . '/node/' . $nid . '/edit';
    $options = ['width' => '90%'];
    $content = self::getContent($host);
    return $preview_response->addCommand(new OpenModalDialogCommand($title, $content, $options));

  }

  /**
   * Loads an external preview page.
   */
  public function getContent($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
  }

  /**
   * Returns url to match environment.
   */
  public function getEnvironment($host, $nid) {
    $url = '';
    switch ($host) {

      case 'dev.cms.va.gov':
        $url = 'http://preview-dev.vfs.va.gov/preview?nodeId=' . $nid;
        break;

      case 'stg.cms.va.gov':
      case 'staging.cms.va.gov':
        $url = 'http://preview-staging.vfs.va.gov/preview?nodeId=' . $nid;
        break;

      case 'cms.va.gov':
      case 'prod.cms.va.gov':
        $url = 'http://preview-prod.vfs.va.gov/preview?nodeId=' . $nid;
        break;

      default:
        $url = '/node/' . $nid . "?_format=static_html";
        break;
    }
    return $url;
  }

}
