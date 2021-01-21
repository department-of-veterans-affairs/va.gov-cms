<?php

namespace Drupal\va_gov_build_trigger\Form;

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
   * Class constructor.
   *
   * @param \Drupal\va_gov_build_trigger\Service\BuildFrontend $buildFrontend
   *   Build the front end service.
   * @param \Drupal\va_gov_build_trigger\WebBuildStatusInterface $webBuildStatus
   *   Webbuild status provider.
   * @param \Drupal\va_gov_build_trigger\Environment\EnvironmentDiscovery $environmentDiscovery
   *   EnvironmentDiscovery service.
   */
  public function __construct(
    BuildFrontend $buildFrontend,
    WebBuildStatusInterface $webBuildStatus,
    EnvironmentDiscovery $environmentDiscovery) {

    $this->buildFrontend = $buildFrontend;
    $this->webBuildStatus = $webBuildStatus;
    $this->environmentDiscovery = $environmentDiscovery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov_build_trigger.build_frontend'),
      $container->get('va_gov.build_trigger.web_build_status'),
      $container->get('va_gov.build_trigger.environment_discovery')
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
    $target = $this->environmentDiscovery->getWebUrl();

    $form['#title'] = $this->t('Manual content release');
    $form['#attached']['library'][] = 'va_gov_build_trigger/build_trigger_form';

    $form['help_1'] = [
      '#prefix' => '<p>',
      '#markup' => $this->t('Any content you set to Published will automatically go live on VA.gov during the daily content release. However, if you need your content to go live sooner, you can perform a manual content release here.'),
      '#suffix' => '</p>',
      '#weight' => -10,
    ];
    $form['help_2'] = [
      '#prefix' => '<p>',
      '#markup' => $this->t('Content release can take up to 15 minutes to finish.'),
      '#suffix' => '</p>',
      '#weight' => -10,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['confirm'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I understand that all VA content set to Published will go live once the release is finished.'),
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Release content now'),
      '#button_type' => 'primary',
      '#attributes' => ['class' => ['is-disabled']],
      '#states' => [
        'disabled' => [':input[name="confirm"]' => ['checked' => FALSE]],
      ],
    ];

    if ($this->webBuildStatus->getWebBuildStatus()) {
      // A build is pending, so set a display.
      $form['tip']['#prefix'] = '<em>';
      $form['tip']['#markup'] = $this->t('A content release has been queued.');
      $form['tip']['#suffix'] = '</em>';
      $form['tip']['#weight'] = 100;
    }

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
    $git_ref = NULL;
    if (
      $field_value = $form_state->getValue('front_end_branch') &&
      // Extract the value in parentheses from the front end branch field value.
      preg_match("/.+\\s\\(([^\\)]+)\\)/", $field_value, $matches)
    ) {
      $git_ref = $matches[1];
    }

    $this->buildFrontend->triggerFrontendBuild($git_ref);
  }

}
