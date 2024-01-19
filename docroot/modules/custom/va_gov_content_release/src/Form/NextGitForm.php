<?php

namespace Drupal\va_gov_content_release\Form;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface;
use Drupal\va_gov_content_release\FrontendVersion\FrontendVersionInterface;
use Drupal\va_gov_content_release\Reporter\ReporterInterface;
use Drupal\va_gov_content_release\Request\RequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A version of the form allowing selection of a Git branch, tag, or commit.
 */
class NextGitForm extends GitForm {

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
    parent::__construct($request, $reporter, $releaseStateManager, $frontendVersion, $blockManager);
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

    $form['build_request']['content_build_selection'] = [
      '#title' => $this->t('Which version of next-build would you like to use?'),
      '#type' => 'radios',
      '#options' => [
        'default' => $this->t('Use default - the next-build version from the time this demo environment was created.'),
        'choose' => $this->t('Select a different next-build branch/pull request - for example, to see your content in a newer frontend design.'),
      ],
      '#default_value' => 'default',
    ];

    $form['build_request']['content_build_git_ref'] = [
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
        'visible' => [':input[name="content_build_selection"]' => ['value' => 'choose']],
      ],
    ];

    return $form;
  }

}
