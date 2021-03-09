<?php

namespace Drupal\va_gov_build_trigger\Form;

use Drupal\Core\Block\BlockManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use Drupal\va_gov_build_trigger\FrontendBuild\DispatcherInterface;
use Drupal\va_gov_build_trigger\FrontendBuild\StatusInterface as FrontendBuildStatusInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements build trigger form.
 *
 * The environment variable CMS_ENVIRONMENT_TYPE is used to determine the URL
 * displayed.
 *
 * You can edit the .env file to change CMS_ENVIRONMENT_TYPE=prod to see what
 * the site will look like in production.
 */
class BuildTriggerForm extends FormBase {

  /**
   * The frontend build dispatcher service.
   *
   * @var \Drupal\va_gov_build_trigger\FrontendBuild\DispatcherInterface
   */
  protected $dispatcher;

  /**
   * The state provider.
   *
   * @var \Drupal\va_gov_build_trigger\FrontendBuild\StatusInterface
   */
  protected $frontendBuildStatus;

  /**
   * EnvironmentDiscovery Service.
   *
   * @var \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery
   */
  protected $environmentDiscovery;

  /**
   * Block Manager Service.
   *
   * @var \Drupal\Core\Block\BlockManager
   */
  protected $blockManager;

  /**
   * Class constructor.
   *
   * @param \Drupal\va_gov_build_trigger\Service\FrontendBuild\DispatcherInterface $dispatcher
   *   Build the front end service.
   * @param \Drupal\va_gov_build_trigger\FrontendBuild\StatusInterface $frontendBuildStatus
   *   Webbuild status provider.
   * @param \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery $environmentDiscovery
   *   EnvironmentDiscovery service.
   * @param \Drupal\Core\Block\BlockManager $blockManager
   *   Block Manager service.
   */
  public function __construct(
    DispatcherInterface $dispatcher,
    FrontendBuildStatusInterface $frontendBuildStatus,
    EnvironmentDiscovery $environmentDiscovery,
    BlockManager $blockManager
  ) {
    $this->dispatcher = $dispatcher;
    $this->frontendBuildStatus = $frontendBuildStatus;
    $this->environmentDiscovery = $environmentDiscovery;
    $this->blockManager = $blockManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov_build_trigger.frontend_build.dispatcher'),
      $container->get('va_gov_build_trigger.frontend_build.status'),
      $container->get('va_gov_build_trigger.environment_discovery'),
      $container->get('plugin.manager.block')
    );
  }

  /**
   * Build the build trigger form.
   *
   * @param array $form
   *   Default form array structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object containing current form state.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'va_gov_build_trigger/build_trigger_form';
    $form['#title'] = $this->t('Release content');
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
    $this->getDispatcher()->triggerFrontendBuild();
  }

  /**
   * Get the dispatcher.
   *
   * @return \Drupal\va_gov_build_trigger\FrontendBuild\DispatcherInterface
   *   The frontend build dispatcher.
   */
  protected function getDispatcher() : DispatcherInterface {
    return $this->dispatcher;
  }

  /**
   * Get the frontend build status service.
   *
   * @return \Drupal\va_gov_build_trigger\FrontendBuild\StatusInterface
   *   The frontend build status service.
   */
  protected function getFrontendBuildStatus() : FrontendBuildStatusInterface {
    return $this->frontendBuildStatus;
  }

  /**
   * Get the environment discovery service.
   *
   * @return \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery
   *   The environment discovery service.
   */
  protected function getEnvironmentDiscovery() : EnvironmentDiscovery {
    return $this->environmentDiscovery;
  }

  /**
   * Get the block manager.
   *
   * @return \Drupal\Core\Block\BlockManager
   *   The block manager service.
   */
  protected function getBlockManager() : BlockManager {
    return $this->environmentDiscovery;
  }

}
