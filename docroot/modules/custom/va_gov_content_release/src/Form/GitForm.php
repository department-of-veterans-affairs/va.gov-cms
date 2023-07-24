<?php

namespace Drupal\va_gov_content_release\Form;

use Drupal\Core\Block\BlockManagerInterface;
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
   * Block Manager Service.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

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
   * @param \Drupal\Core\Block\BlockManager $blockManager
   *   The block manager service.
   */
  public function __construct(
    RequestInterface $request,
    ReporterInterface $reporter,
    ReleaseStateManagerInterface $releaseStateManager,
    FrontendVersionInterface $frontendVersion,
    BlockManagerInterface $blockManager
  ) {
    parent::__construct($request, $reporter, $releaseStateManager);
    $this->frontendVersion = $frontendVersion;
    $this->blockManager = $blockManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov_content_release.request'),
      $container->get('va_gov_content_release.reporter'),
      $container->get('va_gov_build_trigger.release_state_manager'),
      $container->get('va_gov_content_release.frontend_version'),
      $container->get('plugin.manager.block')
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

    $form['content_release_status_block'] = $this->getContentReleaseStatusBlock();

    return $form;
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
   * Submit the build trigger form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('selection') === 'default') {
      $this->resetFrontendVersion($form_state);
    }
    else {
      $this->setFrontendVersion($form_state);
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
    if (empty($this->getGitRef($form_state))) {
      $form_state->setErrorByName('git_ref', $this->t('Invalid selection.'));
    }
  }

  /**
   * Reset the frontend version.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function resetFrontendVersion(FormStateInterface $form_state) {
    if (!$this->isUnderTest($form_state)) {
      $this->frontendVersion->reset();
    }
    else {
      $this->reporter->reportInfo($this->t('Reset frontend version skipped; form is under test.'));
    }
  }

  /**
   * Set the frontend version according to the form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function setFrontendVersion(FormStateInterface $form_state) {
    if (!$this->isUnderTest($form_state)) {
      $this->frontendVersion->set($this->getGitRef($form_state));
    }
    else {
      $this->reporter->reportInfo($this->t('Set frontend version skipped; form is under test.'));
    }
  }

  /**
   * Parse a git ref out of the `git_ref` field value.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   *
   * @return string
   *   A standalone git ref, or an empty string.
   */
  public function getGitRef(FormStateInterface $form_state) : string {
    // If they selected a specific git ref, use that.
    $formValue = $form_state->getValue('git_ref');
    $result = '';
    if (preg_match("/.+\\s\\(([^\\)]+)\\)/", $formValue, $matches)) {
      $result = $matches[1];
    }
    return $result;
  }

}
