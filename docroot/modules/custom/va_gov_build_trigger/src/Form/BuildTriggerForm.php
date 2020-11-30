<?php

namespace Drupal\va_gov_build_trigger\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\va_gov_build_trigger\Service\BuildFrontend;
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
   * @var Drupal\va_gov_build_trigger\Service\BuildFrontend
   */
  protected $buildFrontend;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Class constructor.
   */
  public function __construct(BuildFrontend $buildFrontend, ConfigFactoryInterface $configFactory) {
    $this->buildFrontend = $buildFrontend;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov_build_trigger.build_frontend'),
      $container->get('config.factory')
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
    $environment_type = $this->buildFrontend->getEnvironment();
    $target = $this->buildFrontend->getWebUrl($environment_type);

    $form['actions']['#type'] = 'actions';
    $form['help_1'] = [
      '#prefix' => '<p>',
      '#markup' => t('This is a decoupled Drupal website. Content will not be visible on the Front End until a "Content Release" to an environment.'),
      '#suffix' => '</p>',
      '#weight' => -10,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Release content'),
      '#button_type' => 'primary',
      '#suffix' => ' ' . t('to %site', [
        '%site' => $target,
      ]),
    ];

    // Get pending state.
    $config = $this->configFactory->getEditable('va_gov.build');
    if ($config->get('web.build.pending', 0)) {
      // A build is pending so set a display.
      $form['tip']['#prefix'] = '<em>';
      $form['tip']['#markup'] = t('A content release has been queued.');
      $form['tip']['#suffix'] = '</em>';
      $form['tip']['#weight'] = 100;
    }

    // Case race, first to evaluate TRUE wins.
    switch (TRUE) {
      case $environment_type == 'prod':
      case $environment_type == 'staging':
      case $environment_type == 'dev':
        $description = t('A content release for this environment will be handled by VFS Jenkins.');
        break;

      case $environment_type == 'ci':
        $description = t('A content release for this environment is handled by CMS-CI. You may press this button to trigger a content release. It will take around 45 seconds.');
        break;

      case $environment_type == 'lando':
        $description = t('Content releases within Lando sites must be run manually. Run the following command to regenerate the static site as a content release: <pre>lando composer va:web:build</pre>  The button below is used in CMS and production environments. You can use it to emulate their behavior. You may change the CMS_ENVIRONMENT_TYPE environment behavior to develop.');
        break;

      default:
        $description = t('Environment not detected. Perform a content release by running the <pre>composer va:web:build</pre> command.');
    }

    $target_url = Url::fromUri('http://' . $target, ['attributes' => ['target' => '_blank']]);
    $target_link = Link::fromTextAndUrl($target, $target_url);
    $form['environment_target'] = [
      '#type' => 'item',
      '#title' => t('Environment Target'),
      '#markup' => $target_link->toString(),
      '#description' => $description,
    ];
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
