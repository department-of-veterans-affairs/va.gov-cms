<?php

namespace Drupal\va_gov_build_trigger\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Implements build trigger form overrides for the Tugboat environment.
 */
class TugboatBuildTriggerForm extends BuildTriggerForm {

  /**
   * Build the build trigger form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['description'] = [
      '#prefix' => '<p>',
      '#markup' => $this->t('Release content to update the front end of this demo environment with the latest published content changes.'),
      '#suffix' => '</p>',
      '#weight' => -10,
    ];

    $form['build_request']['title'] = [
      '#type' => 'item',
      '#prefix' => '<h2>',
      '#markup' => $this->t('Request a content release'),
      '#suffix' => '</h2>',
    ];

    $form['build_request']['selection'] = [
      '#title' => $this->t('Which version of the VA.gov front end would you like to use?'),
      '#type' => 'radios',
      '#options' => [
        'default' => $this->t('Use default - the frontend version from the time this demo environment was created.'),
        'choose' => $this->t('Select a different frontend branch/pull request - for example, to see your content in a newer frontend design.'),
      ],
      '#default_value' => 'default',
    ];

    $form['build_request']['git_ref'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Select branch/pull request'),
      '#description' => $this->t('Start typing to select a branch for the frontend version you want to use.'),
      '#autocomplete_route_name' => 'va_gov_build_trigger.front_end_branches_autocomplete',
      '#autocomplete_route_parameters' => [
        'count' => 10,
      ],
      '#size' => 72,
      '#maxlength' => 1024,
      '#hidden' => TRUE,
      '#states' => [
        'visible' => [':input[name="selection"]' => ['value' => 'choose']],
      ],
    ];

    $form['build_request']['actions']['#type'] = 'actions';
    $form['build_request']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Release content'),
      '#button_type' => 'primary',
    ];

    $form['content_release_status_block'] = $this->getContentReleaseStatusBlock();

    $help_url = Url::fromUri(
      'https://va-gov.atlassian.net/servicedesk/customer/portal/3/group/8/create/26',
      ['attributes' => ['target' => '_blank']]
    );
    $help_link = Link::fromTextAndUrl($this->t('contact the CMS help desk'), $help_url);
    $description = $this->t(
      'It may take up to one minute for the status of new content releases to be reflected here. If you encounter an error, please @help_link.',
      ['@help_link' => $help_link->toString()]
    );
    $form['content_release_status_help'] = [
      '#type' => 'item',
      // '#title' => $this->t('Recent content releases'),
      '#description' => $description,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('selection') === 'default') {
      return;
    }

    $git_ref_value = $form_state->getValue('git_ref');
    $git_ref = $this->getGitRef($git_ref_value);

    if (empty($git_ref)) {
      $form_state->setErrorByName('git_ref', $this->t('Invalid selection.'));
    }
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
    if ($form_state->getValue('selection') === 'default') {
      // If they want the default version, reset to whatever is specified in
      // composer.json.
      $this->buildRequester->resetFrontendVersion();
    }
    else {
      // If they selected a specific git ref, use that.
      $git_ref_value = $form_state->getValue('git_ref');
      $git_ref = $this->getGitRef($git_ref_value);
      $this->buildRequester->switchFrontendVersion($git_ref);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * Get the rendered content release status block.
   *
   * @return array
   *   Block render array.
   */
  protected function getContentReleaseStatusBlock() {
    return $this->blockManager
      ->createInstance('content_release_status_block', [])
      ->build();
  }

  /**
   * Parse a git ref out of the git_ref field value.
   *
   * @param string $git_ref_value
   *   The contents of the git ref field.
   *
   * @return string
   *   A standalone git ref.
   */
  protected function getGitRef($git_ref_value) : string {
    $git_ref = '';
    if ($git_ref_value && preg_match("/.+\\s\\(([^\\)]+)\\)/", $git_ref_value, $matches)) {
      $git_ref = $matches[1];
    }

    return $git_ref;
  }

}
