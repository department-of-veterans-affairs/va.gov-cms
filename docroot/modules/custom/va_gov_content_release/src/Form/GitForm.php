<?php

namespace Drupal\va_gov_content_release\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_content_release\FrontendVersion\FrontendVersionInterface;
use Drupal\va_gov_content_release\Request\RequestInterface;
use Drupal\va_gov_content_release\Reporter\ReporterInterface;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A version of the form allowing selection of a Git branch, tag, or commit.
 */
class GitForm extends BaseForm {

  /**
   * The frontend version service.
   *
   * @var \Drupal\va_gov_content_release\FrontendVersion\FrontendVersionInterface
   */
  protected $frontendVersion;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_content_release\Request\RequestInterface $request
   *   Request service.
   * @param \Drupal\va_gov_content_release\Reporter\ReporterInterface $reporter
   *   Reporter service.
   * @param \Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface $releaseStateManager
   *   Release state manager service.
   * @param \Drupal\va_gov_content_release\FrontendVersion\FrontendVersionInterface $frontendVersion
   *   The frontend version service.
   */
  public function __construct(
    RequestInterface $request,
    ReporterInterface $reporter,
    ReleaseStateManagerInterface $releaseStateManager,
    FrontendVersionInterface $frontendVersion
  ) {
    parent::__construct($request, $reporter, $releaseStateManager);
    $this->frontendVersion = $frontendVersion;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov_content_release.request'),
      $container->get('va_gov_content_release.reporter'),
      $container->get('va_gov_build_trigger.release_state_manager'),
      $container->get('va_gov_content_release.frontend_version')
    );
  }

  /**
   * Build the form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['build_request']['actions']['#type'] = 'actions';
    $form['build_request']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Release content'),
      '#button_type' => 'primary',
    ];

    $form['description'] = [
      '#prefix' => '<p>',
      '#markup' => $this->t('Release content to update the front end of this environment with the latest published content changes.'),
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
    if ($form_state->getValue('selection') === 'default') {
      $this->resetFrontendVersion();
    }
    else {
      // If they selected a specific git ref, use that.
      $gitRefValue = $form_state->getValue('git_ref');
      $gitRef = $this->getGitRef($gitRefValue);
      $this->setFrontendVersion($gitRef);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('selection') === 'default') {
      return;
    }
    $gitRefFormValue = $form_state->getValue('git_ref');
    $gitRef = $this->getGitRef($gitRefFormValue);

    if (empty($gitRef)) {
      $form_state->setErrorByName('git_ref', $this->t('Invalid selection.'));
    }
  }

  /**
   * Reset the frontend version.
   */
  public function resetFrontendVersion() {
    $this->frontendVersion->reset();
  }

  /**
   * Set the frontend version to the specified value.
   *
   * @param string $gitRef
   *   The git ref to set.
   */
  public function setFrontendVersion(string $gitRef) {
    $this->frontendVersion->set($gitRef);
  }

  /**
   * Parse a git ref out of the `git_ref` field value.
   *
   * @param string $formValue
   *   The contents of the git ref field.
   *
   * @return string
   *   A standalone git ref.
   */
  public function getGitRef(string $formValue = '') : string {
    $result = '';
    if (preg_match("/.+\\s\\(([^\\)]+)\\)/", $formValue, $matches)) {
      $result = $matches[1];
    }
    return $result;
  }

}
