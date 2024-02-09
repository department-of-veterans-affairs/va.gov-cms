<?php

namespace Drupal\va_gov_content_release\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\State\State;
use Drupal\Core\Url;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManager;
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
   * File system service.
   */
  protected FileSystemInterface $fileSystem;

  /**
   * The config service.
   */
  protected ConfigFactory $config;

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
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   The settings service.
   * @param \Drupal\Core\State\State $state
   *   The state service.
   */
  public function __construct(
    FrontendVersionInterface $frontendVersion,
    FileSystemInterface $fileSystem,
    ConfigFactory $config,
    State $state
  ) {
    $this->frontendVersion = $frontendVersion;
    $this->fileSystem = $fileSystem;
    $this->config = $config;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov_content_release.frontend_version'),
      $container->get('file_system'),
      $container->get('config.factory'),
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
      '#value' => $this->t('Release Content'),
      '#button_type' => 'primary',
    ];

    // Lock the vets-website form fields if a content-build is in progress.
    $build_status = $this->state->get('va_gov_build_trigger.release_state');
    if ($build_status !== ReleaseStateManager::STATE_READY) {
      $form['build_request']['vets_website_selection']['#disabled'] = TRUE;
      $form['build_request']['vets_website_git_ref']['#disabled'] = TRUE;
    }

    // Disable form changes and submission if a build is in progress.
    if (file_exists($this->fileSystem->realpath('public://' . self::LOCK_FILE_NAME))) {
      $form['build_request']['next_build_selection']['#disabled'] = TRUE;
      $form['build_request']['next_build_git_ref']['#disabled'] = TRUE;
      $form['build_request']['vets_website_selection']['#disabled'] = TRUE;
      $form['build_request']['vets_website_git_ref']['#disabled'] = TRUE;
      $form['build_request']['actions']['submit']['#disabled'] = TRUE;

      $target_url = Url::fromUserInput("/sites/default/files/next-build.txt");
      $build_log_text = Link::fromTextAndUrl('Build is in progress. View log file', $target_url);
    }
    else {
      $build_log_text = 'Build is not in progress.';
    }

    // Set variables needed for build status information.
    $lock_file_text = $this->getFileLink(self::LOCK_FILE_NAME);
    $request_file_text = $this->getFileLink(self::REQUEST_FILE_NAME);
    $next_build_version = $this->frontendVersion->getVersion(Frontend::NextBuild);
    $vets_website_version = $this->frontendVersion->getVersion(Frontend::VetsWebsite);
    $view_preview = $this->getPreviewLink();
    $last_build_time = $this->state->get('next_build.status.last_build_date', 'N/A');
    $form['content_release_status_block'] = [
      '#theme' => 'status_report_grouped',
      '#grouped_requirements' => [
        [
          'title' => $this->t('Next Build Information'),
          'type' => 'content-release-status',
          'items' => [
            'status' => [
              'title' => $this->t('Status'),
              'value' => $build_log_text,
            ],
            'lock_file' => [
              'title' => $this->t('Lock File'),
              'value' => $lock_file_text,
            ],
            'request_file' => [
              'title' => $this->t('Request File'),
              'value' => $request_file_text,
            ],
            'next_build_version' => [
              'title' => $this->t('Next-build Version'),
              'value' => $next_build_version,
            ],
            'vets_website_version' => [
              'title' => $this->t('Vets-website Version'),
              'value' => $vets_website_version,
            ],
            'view_preview' => [
              'title' => $this->t('View Preview'),
              'value' => $view_preview,
            ],
            'last_build_time' => [
              'title' => $this->t('Last Build Time'),
              'value' => $last_build_time,
            ],
          ],
        ],
      ],
    ];

    return $form;
  }

  /**
   * Get the text for a file.
   *
   * @param string $file_name
   *   The name of the file.
   *
   * @return \Drupal\Core\Link|string
   *   The file link.
   */
  private function getFileLink(string $file_name): Link|string {
    $file_path = $this->fileSystem->realpath("public://$file_name");
    if (file_exists($file_path)) {
      $target_url = Url::fromUserInput("/sites/default/files/$file_name");
      return Link::fromTextAndUrl($file_name, $target_url);
    }
    else {
      return 'does not exist';
    }
  }

  /**
   * Get the preview link.
   *
   * @return \Drupal\Core\Link
   *   The preview link.
   */
  private function getPreviewLink(): Link {
    $frontend_base_url = $this->config
      ->get('next.next_site.next_build_preview_server')
      ->get('base_url');
    $target_url = Url::fromUri($frontend_base_url, ['attributes' => ['target' => '_blank']]);
    return Link::fromTextAndUrl($this->t('View front end'), $target_url);
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
