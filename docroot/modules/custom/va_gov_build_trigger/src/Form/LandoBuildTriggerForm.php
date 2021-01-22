<?php

namespace Drupal\va_gov_build_trigger\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Implements build trigger form overrides for the Lando environment.
 */
class LandoBuildTriggerForm extends BuildTriggerForm {

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

    $form['#title'] = $this->t('Release content');

    $form['help_1']['#markup'] = $this->t('Release content to update the front end of this local environment with the latest published content changes.');
    unset($form['help_2']);
    unset($form['actions']['confirm']);
    $form['actions']['submit']['#value'] = $this->t('Update');

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
        'default' => $this->t('Use default - the front end version from the time this local environment was created.'),
        'choose_branch' => $this->t('Select a different front end branch/pull request - for example, to see your content in a newer front end design.'),
      ],
      '#default_value' => 'default',
    ];
    $form['section_1']['branch'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Select branch/pull request'),
      '#description' => $this->t('Start typing to select a branch for the front end version you want to use.'),
      '#autocomplete_route_name' => 'va_gov_build_trigger.front_end_branches_autocomplete',
      '#autocomplete_route_parameters' => [
        'count' => 10,
      ],
      '#size' => 72,
      '#maxlength' => 1024,
      '#hidden' => TRUE,
      '#states' => [
        'visible' => [':input[name="selection"]' => ['value' => 'choose_branch']],
      ],
    ];

    if ($this->webBuildStatus->useContentExport()) {
      $form['section_1']['full_rebuild'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Trigger a full Content Export Rebuild.'),
        '#states' => [
          'visible' => [':input[name="selection"]' => ['value' => 'choose_branch']],
        ],
      ];
    }

    $description = $this->t('Content releases within Lando. You may press this button to trigger a content release. Please note this could take several minutes to run.');
    $form['section_1']['actions']['submit'] = $form['actions']['submit'];
    unset($form['actions']['submit']);

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
      'It may take up to one minute for the status of new content releases to appear here. If you encounter an error, please @help_link.',
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
      '#markup' => $this->t('3. Access the front end environment'),
      '#suffix' => '</h2>',
    ];
    $target = $this->environmentDiscovery->getWebUrl();
    $target_url = Url::fromUri($target, ['attributes' => ['target' => '_blank']]);
    $target_link = Link::fromTextAndUrl($this->t('front end URL for this local environment'), $target_url);
    $description = $this->t(
      'Once the release is completed successfully, use the @target_link to see how your content will appear to site visitors.',
      ['@target_link' => $target_link->toString()]
    );
    $form['section_3']['environment_target'] = [
      '#type' => 'item',
      '#markup' => $description,
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
    $front_end_branch = $form_state->getValue('branch');
    $full_rebuild = (bool) $form_state->getValue('full_rebuild');
    $git_ref = NULL;
    if ($front_end_branch && preg_match("/.+\\s\\(([^\\)]+)\\)/", $front_end_branch, $matches)) {
      $git_ref = $matches[1];
    }

    $this->buildFrontend->triggerFrontendBuild($git_ref, $full_rebuild);
  }

}
