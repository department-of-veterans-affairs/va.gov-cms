<?php

namespace Drupal\va_gov_content_release\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface;
use Drupal\va_gov_content_release\Request\RequestInterface;
use Drupal\va_gov_content_release\Reporter\ReporterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Content release base form.
 */
class BaseForm extends FormBase {

  const HELP_DESK_URL = 'https://va-gov.atlassian.net/servicedesk/customer/portal/3/group/8/create/26';

  const FORM_ID = 'va_gov_content_release_form';

  /**
   * Request service.
   *
   * @var \Drupal\va_gov_content_release\Request\RequestInterface
   */
  protected $request;

  /**
   * Reporter service.
   *
   * @var \Drupal\va_gov_content_release\Reporter\ReporterInterface
   */
  protected $reporter;

  /**
   * Release state manager service.
   *
   * @var \Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface
   */
  protected $releaseStateManager;

  /**
   * Constructor.
   *
   * @param \Drupal\va_gov_content_release\Request\RequestInterface $request
   *   Request service.
   * @param \Drupal\va_gov_content_release\Reporter\ReporterInterface $reporter
   *   Reporter service.
   * @param \Drupal\va_gov_build_trigger\Service\ReleaseStateManagerInterface $releaseStateManager
   *   Release state manager service.
   */
  public function __construct(
    RequestInterface $request,
    ReporterInterface $reporter,
    ReleaseStateManagerInterface $releaseStateManager
  ) {
    $this->request = $request;
    $this->reporter = $reporter;
    $this->releaseStateManager = $releaseStateManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('va_gov_content_release.request'),
      $container->get('va_gov_content_release.reporter'),
      $container->get('va_gov_build_trigger.release_state_manager')
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
    $form['#attached']['library'][] = 'va_gov_content_release/form';
    $form['#title'] = $this->t('Release content');

    if ($this->releaseStateManager->releaseIsImminent()) {
      // A build is pending, so set a display.
      $form['tip']['#prefix'] = '<em>';
      $form['tip']['#markup'] = $this->t('A content release will start soon.');
      $form['tip']['#suffix'] = '</em>';
      $form['tip']['#weight'] = 100;
    }

    $form['content_release_status_help'] = [
      '#type' => 'item',
      '#description' => $this->getHelp(),
    ];

    return $form;
  }

  /**
   * Get a specially formatted help message.
   *
   * @return string
   *   The help message.
   */
  public function getHelp(): string {
    $helpDeskUrl = Url::fromUri(self::HELP_DESK_URL, [
      'attributes' => ['target' => '_blank'],
    ]);
    $helpDeskLink = Link::fromTextAndUrl($this->t('contact the CMS help desk'), $helpDeskUrl);
    return $this->t(
      'It may take up to one minute for the status of new content releases to be reflected here. If you encounter an error, please @help_link.',
      ['@help_link' => $helpDeskLink->toString()]
    );
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
    return self::FORM_ID;
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
    $this->reporter->reportInfo($this->t('Content release requested successfully.'));
    $this->request->submitRequest('Build requested via form.');
  }

}
