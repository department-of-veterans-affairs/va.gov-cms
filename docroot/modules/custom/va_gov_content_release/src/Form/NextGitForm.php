<?php

namespace Drupal\va_gov_content_release\Form;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\State\State;
use Drupal\va_gov_content_release\Frontend\Frontend;
use Drupal\va_gov_content_release\Frontend\FrontendInterface;
use Drupal\va_gov_content_release\FrontendVersion\FrontendVersionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A version of the form allowing selection of a Git branch, tag, or commit.
 */
class NextGitForm extends FormBase {

  const LOCK_FILE_NAME = 'next-buildlock.txt';
  const REQUEST_FILE_NAME = 'next-buildrequest.txt';

  /**
   * The frontend version service.
   */
  protected FrontendVersionInterface $frontendVersion;

  /**
   * File System Service.
   */
  protected FileSystemInterface $fileSystem;

  /**
   * The settings service.
   */
  protected Settings $settings;

  /**
   * The state service.
   */
  protected State $state;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_content_release\FrontendVersion\FrontendVersionInterface $frontendVersion
   *   The frontend version service.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system service.
   * @param \Drupal\Core\Site\Settings $settings
   *   The settings service.
   * @param \Drupal\Core\State\State $state
   *   The state service.
   */
  public function __construct(
    FrontendVersionInterface $frontendVersion,
    FileSystemInterface $fileSystem,
    Settings $settings,
    State $state
  ) {
    $this->frontendVersion = $frontendVersion;
    $this->fileSystem = $fileSystem;
    $this->settings = $settings;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov_content_release.frontend_version'),
      $container->get('file_system'),
      $container->get('settings'),
      $container->get('state')
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
    $form['build_request']['description'] = [
      '#prefix' => '<br><p>',
      '#markup' => $this->t('Release content to update the front end of this environment with the latest published content changes.'),
      '#suffix' => '</p>',
    ];

    $form['build_request']['next_build_selection'] = [
      '#title' => $this->t('Which version of next-build would you like to use?'),
      '#type' => 'radios',
      '#options' => [
        'default' => $this->t('Use default - the next-build version from the time this demo environment was created.'),
        'choose' => $this->t('Select a different next-build branch/pull request - for example, to see your content in a newer frontend design.'),
      ],
      '#default_value' => 'default',
    ];

    $form['build_request']['next_build_git_ref'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Select branch/pull request'),
      '#description' => $this->t('Start typing to select a branch for the next-build version you want to use.'),
      '#autocomplete_route_name' => 'va_gov_content_release.frontend_version_autocomplete',
      '#autocomplete_route_parameters' => [
        'frontend' => 'next_build',
        'count' => 10,
      ],
      '#size' => 72,
      '#maxlength' => 1024,
      '#hidden' => TRUE,
      '#states' => [
        'visible' => [':input[name="next_build_selection"]' => ['value' => 'choose']],
      ],
    ];

    $form['build_request']['vets_website_selection'] = [
      '#title' => $this->t('Which version of vets-website would you like to use?'),
      '#type' => 'radios',
      '#options' => [
        'default' => $this->t('Use default - the vets-website version from the time this demo environment was created.'),
        'choose' => $this->t('Select a different vets-website branch/pull request - for example, to see your content in a newer frontend design.'),
      ],
      '#default_value' => 'default',
    ];

    $form['build_request']['vets_website_git_ref'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Select branch/pull request'),
      '#description' => $this->t('Start typing to select a branch for the vets-website version you want to use.'),
      '#autocomplete_route_name' => 'va_gov_content_release.frontend_version_autocomplete',
      '#autocomplete_route_parameters' => [
        'frontend' => 'vets_website',
        'count' => 10,
      ],
      '#size' => 72,
      '#maxlength' => 1024,
      '#hidden' => TRUE,
      '#states' => [
        'visible' => [':input[name="vets_website_selection"]' => ['value' => 'choose']],
      ],
    ];

    $form['build_request']['actions']['#type'] = 'actions';
    $form['build_request']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Release content'),
      '#button_type' => 'primary',
    ];

    $form['next_build_status'] = [
      '#type' => 'container',
      '#attributes' => [
        'style' => 'background-color: #f2f2f2; padding: 20px; border: 1px solid #ccc;',
      ],
    ];

    $form['next_build_status']['title'] = [
      '#type' => 'item',
      '#prefix' => '<strong>',
      '#markup' => $this->t('Next Build Information:'),
      '#suffix' => '</strong>',
    ];

    // Disable form changes and submission if a build is in progress.
    if (file_exists($this->fileSystem->realpath('public://' . self::LOCK_FILE_NAME))) {
      $form['build_request']['next_build_selection']['#disabled'] = TRUE;
      $form['build_request']['next_build_git_ref']['#disabled'] = TRUE;
      $form['build_request']['vets_website_selection']['#disabled'] = TRUE;
      $form['build_request']['vets_website_git_ref']['#disabled'] = TRUE;
      $form['build_request']['actions']['submit']['#disabled'] = TRUE;

      $build_log_text = 'Build is in progress. View log file: ' .
        '<a target="_blank" href="/sites/default/files/next-build.txt">Next Build Log</a>.';
    }
    else {
      $build_log_text = 'Build is not in progress.';
    }

    // Set variables needed for build status information.
    $lock_file_text = $this->getFileText(self::LOCK_FILE_NAME);
    $request_file_text = $this->getFileText(self::REQUEST_FILE_NAME);
    $next_build_version = $this->frontendVersion->getVersion(Frontend::NextBuild);
    $vets_website_version = $this->frontendVersion->getVersion(Frontend::VetsWebsite);
    $view_preview = $this->getPreviewLink();
    $last_build_time = $this->state->get('next_build.status.last_build_date', 'N/A');
    $information = <<<HTML
      <p><strong>Status:</strong> $build_log_text</p>
      <p><strong>Lock file:</strong> $lock_file_text</p>
      <p><strong>Request file:</strong> $request_file_text</p>
      <p><strong>Next-build version:</strong> $next_build_version</p>
      <p><strong>Vets-website version:</strong> $vets_website_version</p>
      <p><strong>View preview:</strong> $view_preview</p>
      <p><strong>Last build time:</strong> $last_build_time</p>
HTML;

    $form['next_build_status']['information'] = [
      '#markup' => $information,
    ];

    return $form;
  }

  /**
   * Get the text for a file.
   *
   * @param string $file_name
   *   The name of the file.
   *
   * @return string
   *   The text for the file.
   */
  private function getFileText(string $file_name): string {
    $file_path = $this->fileSystem->realpath("public://$file_name");
    if (file_exists($file_path)) {
      return "<a target='_blank' href=\"/sites/default/files/$file_name\">$file_name</a>";
    }
    else {
      return 'does not exist';
    }
  }

  /**
   * Get the preview link.
   *
   * @return string
   *   The preview link.
   */
  private function getPreviewLink(): string {
    $frontendBaseUrl = $this->settings->get('va_gov_frontend_url') ?? 'https://www.va.gov';
    return "<a target='_blank' href=\"$frontendBaseUrl\">$frontendBaseUrl</a>";
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
    $this->submitFormForFrontend(Frontend::NextBuild, $form_state);
    $this->submitFormForFrontend(Frontend::VetsWebsite, $form_state);

    $lock_file = $this->fileSystem->realpath('public://' . self::LOCK_FILE_NAME);
    if (file_exists($lock_file)) {
      $this->messenger()
        ->addMessage($this->t('The build is in progress. Please wait for the build to complete.'));
    }
    else {
      $this->fileSystem->saveData(
        'Build me, Seymour!',
        'public://' . self::REQUEST_FILE_NAME,
        1);
      $this->messenger()->addMessage($this->t('Build request file set.'));
    }
  }

  /**
   * Submit the form.
   *
   * @param \Drupal\va_gov_content_release\Frontend\FrontendInterface $frontend
   *   The frontend whose version we are managing.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  protected function submitFormForFrontend(
    FrontendInterface $frontend,
    FormStateInterface $form_state
  ) {
    $selectionName = $frontend->getRawValue() . '_selection';
    if ($form_state->getValue($selectionName) === 'default') {
      $this->resetFrontendVersion($frontend, $form_state);
    }
    else {
      $this->setFrontendVersion($frontend, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->validateFormForFrontend(Frontend::NextBuild, $form_state);
    $this->validateFormForFrontend(Frontend::VetsWebsite, $form_state);
  }

  /**
   * Validate the form.
   *
   * @param \Drupal\va_gov_content_release\Frontend\FrontendInterface $frontend
   *   The frontend whose version we are managing.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  protected function validateFormForFrontend(
    FrontendInterface $frontend,
    FormStateInterface $form_state
  ) {
    $selectionName = $frontend->getRawValue() . '_selection';
    $gitRefName = $frontend->getRawValue() . '_git_ref';
    if ($form_state->getValue($selectionName) !== 'default') {
      if (empty($this->getGitRef($frontend, $form_state))) {
        $form_state->setErrorByName($gitRefName,
          $this->t('Invalid selection.'));
      }
    }
  }

  /**
   * Reset the frontend version.
   *
   * @param \Drupal\va_gov_content_release\Frontend\FrontendInterface $frontend
   *   The frontend whose version we are resetting.
   */
  public function resetFrontendVersion(FrontendInterface $frontend) {
    $this->frontendVersion->resetVersion($frontend);
  }

  /**
   * Set the frontend version according to the form.
   *
   * @param \Drupal\va_gov_content_release\Frontend\FrontendInterface $frontend
   *   The frontend whose version we are setting.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function setFrontendVersion(
    FrontendInterface $frontend,
    FormStateInterface $form_state
  ) {
    $this->frontendVersion->setVersion($frontend,
      $this->getGitRef($frontend, $form_state));
  }

  /**
   * Parse a git ref out of the `git_ref` field value.
   *
   * @param \Drupal\va_gov_content_release\Frontend\FrontendInterface $frontend
   *   The frontend whose version we are setting.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   *
   * @return string
   *   A standalone git ref, or an empty string.
   */
  public function getGitRef(
    FrontendInterface $frontend,
    FormStateInterface $form_state
  ): string {
    // If they selected a specific git ref, use that.
    $gitRefName = $frontend->getRawValue() . '_git_ref';
    $formValue = $form_state->getValue($gitRefName);
    $result = '';
    if (preg_match("/.+\\s\\(([^\\)]+)\\)/", $formValue, $matches)) {
      $result = $matches[1];
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'va_gov_content_release_next_git_form';
  }

}
