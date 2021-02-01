<?php

namespace Drupal\va_gov_build_trigger\Form;

use Drupal\Core\Block\BlockManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery;
use Drupal\va_gov_build_trigger\Service\BuildFrontend;
use Drupal\va_gov_build_trigger\WebBuildStatusInterface;
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
   * The front-end build service.
   *
   * @var \Drupal\va_gov_build_trigger\Service\BuildFrontend
   */
  protected $buildFrontend;

  /**
   * The state provider.
   *
   * @var \Drupal\va_gov_build_trigger\WebBuildStatusInterface
   */
  protected $webBuildStatus;

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
   * @param \Drupal\va_gov_build_trigger\Service\BuildFrontend $buildFrontend
   *   Build the front end service.
   * @param \Drupal\va_gov_build_trigger\WebBuildStatusInterface $webBuildStatus
   *   Webbuild status provider.
   * @param \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery $environmentDiscovery
   *   EnvironmentDiscovery service.
   * @param \Drupal\Core\Block\BlockManager $blockManager
   *   Block Manager service.
   */
  public function __construct(
    BuildFrontend $buildFrontend,
    WebBuildStatusInterface $webBuildStatus,
    EnvironmentDiscovery $environmentDiscovery,
    BlockManager $blockManager) {

    $this->buildFrontend = $buildFrontend;
    $this->webBuildStatus = $webBuildStatus;
    $this->environmentDiscovery = $environmentDiscovery;
    $this->blockManager = $blockManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov_build_trigger.build_frontend'),
      $container->get('va_gov.build_trigger.web_build_status'),
      $container->get('va_gov.build_trigger.environment_discovery'),
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
    $this->buildFrontend->triggerFrontendBuild();
  }

}
