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

    $form['help_1'] = [
      '#prefix' => '<p>',
      '#markup' => $this->t('Release content to update the front end of this demo environment with the latest published content changes.'),
      '#suffix' => '</p>',
      '#weight' => -10,
    ];

    $form['section_1']['title'] = [
      '#type' => 'item',
      '#prefix' => '<h2>',
      '#markup' => $this->t('1. Release content'),
      '#suffix' => '</h2>',
    ];
    $form['section_1']['selection'] = [
      '#title' => $this->t('Which version of the VA.gov front end would you like to use?'),
      '#type' => 'radios',
      '#options' => [
        'default' => $this->t('Use default - the frontend version from the time this demo environment was created.'),
        'choose' => $this->t('Select a different frontend branch/pull request - for example, to see your content in a newer frontend design.'),
      ],
      '#default_value' => 'default',
    ];
    $form['section_1']['git_ref'] = [
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

    $form['section_1']['actions']['#type'] = 'actions';
    $form['section_1']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Release content'),
      '#button_type' => 'primary',
    ];

    $form['section_2']['title'] = [
      '#type' => 'item',
      '#prefix' => '<h2>',
      '#markup' => $this->t('2. Wait for the release to complete'),
      '#suffix' => '</h2>',
    ];
    $help_url = Url::fromUri(
      'https://va-gov.atlassian.net/servicedesk/customer/portal/3/group/8/create/26',
      ['attributes' => ['target' => '_blank']]
    );
    $help_link = Link::fromTextAndUrl($this->t('contact the CMS help desk'), $help_url);
    $description = $this->t(
      'It may take up to one minute for the status of new content releases to appear here in the queue. Content releases will complete in the order released. If you encounter an error, please @help_link.',
      ['@help_link' => $help_link->toString()]
    );
    $form['section_2']['help'] = [
      '#type' => 'item',
      '#title' => $this->t('Recent content releases'),
      '#description' => $description,
    ];
    $form['section_2']['content_release_status_block'] = $this->getContentReleaseStatusBlock();

    $form['section_3']['title'] = [
      '#type' => 'item',
      '#prefix' => '<h2>',
      '#markup' => $this->t('3. Access the frontend environment'),
      '#suffix' => '</h2>',
    ];
    $description = $this->t('Once the release is completed successfully, see how your content will appear to site visitors on the front end.');
    $form['section_3']['environment_target'] = [
      '#type' => 'item',
      '#markup' => $description,
    ];
    $target = $this->environmentDiscovery->getWebUrl();
    $target_url = Url::fromUri($target, ['attributes' => ['target' => '_blank']]);
    $target_link = Link::fromTextAndUrl($this->t('Go to front end'), $target_url);
    $form['section_3']['cta'] = [
      '#type' => 'item',
      '#wrapper_attributes' => ['class' => ['button button--primary']],
      '#markup' => $target_link->toString(),
    ];

    return $form;
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
    $full_rebuild = (bool) $form_state->getValue('full_rebuild');
    $git_ref = NULL;
    $git_ref_value = $form_state->getValue('git_ref');
    if ($git_ref_value && preg_match("/.+\\s\\(([^\\)]+)\\)/", $git_ref_value, $matches)) {
      $git_ref = $matches[1];
    }

    $this->buildFrontend->triggerFrontendBuild($git_ref, $full_rebuild);
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

}
