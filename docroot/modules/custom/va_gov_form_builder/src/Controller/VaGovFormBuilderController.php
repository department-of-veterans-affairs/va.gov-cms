<?php

namespace Drupal\va_gov_form_builder\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the VA Form Builder experience.
 *
 * Note:
 *
 * Drupal also has a something called "form builder"
 * that is utilized in this controller (`drupalFormBuilder`).
 * This `FormBuilder` service is responsible for building and
 * processing forms in Drupal, and is called here so the controller can
 * return the form that should be rendered for the given route.
 *
 * The naming can be confusing, so it's important to remember that
 * `VaGovFormBuilder` is the tool built by this module (`va_gov_form_builder`),
 * and used for building Veteran-facing VA forms, while Drupal's
 * `FormBuilder` (service) is responsible for building and processing
 * forms _within_ Drupal.
 */
class VaGovFormBuilderController extends ControllerBase {

  /**
   * The prefix for the page-content theme definitions.
   */
  const PAGE_CONTENT_THEME_PREFIX = 'page_content__va_gov_form_builder__';

  /**
   * The prefix for the page-specific style libraries.
   */
  const LIBRARY_PREFIX = 'va_gov_form_builder/va_gov_form_builder_styles__';

  /**
   * The Drupal form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  private $drupalFormBuilder;

  /**
   * The Digital Forms service.
   *
   * @var \Drupal\va_gov_form_builder\Service\DigitalFormsService
   */
  private $digitalFormsService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->drupalFormBuilder = $container->get('form_builder');
    $instance->digitalFormsService = $container->get('va_gov_form_builder.digital_forms_service');

    return $instance;
  }

  /**
   * Returns a render array representing the page with the passed-in content.
   *
   * @param array $pageContent
   *   A render array representing the page content.
   * @param string $subtitle
   *   The subtitle for the page.
   * @param string $libraries
   *   Libraries for the page, in addition to the Form Builder general library,
   *   which is added automatically.
   */
  private function getPage($pageContent, $subtitle, $libraries = NULL) {
    $page = [
      '#type' => 'page',
      'content' => $pageContent,
      // Add custom data.
      'form_builder_page_data' => [
        'subtitle' => $subtitle,
      ],
      // Add styles.
      '#attached' => [
        'library' => [
          'va_gov_form_builder/va_gov_form_builder_styles',
        ],
      ],
    ];

    if (!empty($libraries)) {
      foreach ($libraries as $library) {
        $page['#attached']['library'][] = self::LIBRARY_PREFIX . $library;
      }
    }

    return $page;
  }

  /**
   * Returns a render array representing the page with the passed-in form.
   *
   * @param string $formName
   *   The filename of the form to be rendered.
   * @param string $subtitle
   *   The subtitle for the page.
   * @param string $libraries
   *   Libraries for the page, in addition to the Form Builder general library,
   *   which is added automatically.
   * @param string $nid
   *   The node id, passed in when the form in question edits an existing node.
   */
  private function getFormPage($formName, $subtitle, $libraries = NULL, $nid = NULL) {
    // @phpstan-ignore-next-line
    $form = $this->drupalFormBuilder->getForm('Drupal\va_gov_form_builder\Form\\' . $formName, $nid);

    return $this->getPage($form, $subtitle, $libraries);
  }

  /**
   * Entry point for the VA Form Builder. Redirects to the home page.
   */
  public function entry() {
    return $this->redirect('va_gov_form_builder.home');
  }

  /**
   * Home page.
   */
  public function home() {
    // Passing "FALSE" to fetch draft nodes rather than only published nodes.
    $digitalForms = $this->digitalFormsService->getDigitalForms(FALSE);

    $recentForms = [];
    foreach ($digitalForms as $digitalForm) {
      $recentForms[] = [
        'nid' => $digitalForm->id(),
        'title' => $digitalForm->getTitle(),
        'formNumber' => $digitalForm->get('field_va_form_number')->value,
      ];
    }

    $pageContent = [
      '#theme' => self::PAGE_CONTENT_THEME_PREFIX . 'home',
      '#build_form_url' => Url::fromRoute('va_gov_form_builder.start_conversion')->toString(),
      '#recent_forms' => $recentForms,
    ];
    $subtitle = 'Select a form';
    $libraries = ['home'];

    return $this->getPage($pageContent, $subtitle, $libraries);
  }

  /**
   * Start-conversion page.
   */
  public function startConversion() {
    $formName = 'StartConversion';
    $subtitle = 'Subtitle Placeholder';
    return $this->getFormPage($formName, $subtitle);
  }

  /**
   * Name-and-date-of-birth page.
   */
  public function nameAndDob($nid) {
    $formName = 'NameAndDob';
    $subtitle = 'Subtitle Placeholder';
    return $this->getFormPage($formName, $subtitle, NULL, $nid);
  }

}
