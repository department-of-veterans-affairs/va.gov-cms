<?php

namespace Drupal\va_gov_form_builder\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Drupal form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $drupalFormBuilder;

  /**
   * The Digital Forms service.
   *
   * @var \Drupal\va_gov_form_builder\Service\DigitalFormsService
   */
  protected $digitalFormsService;

  /**
   * The Digital Form object.
   *
   * When the page in question edits or references an existing
   * digital form node, this property is populated. When the
   * page creates a new digital form node or otherwise does
   * not reference a node, this is empty.
   *
   * @var \Drupal\va_gov_form_builder\EntityWrapper\DigitalForm|null
   */
  protected $digitalForm;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);

    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->drupalFormBuilder = $container->get('form_builder');
    $instance->digitalFormsService = $container->get('va_gov_form_builder.digital_forms_service');

    return $instance;
  }

  /**
   * Loads and sets the Digital Form object.
   *
   * @param string $nid
   *   The node id to load.
   *
   * @return bool
   *   TRUE if successfully loaded. FALSE otherwise.
   */
  protected function loadDigitalForm($nid) {
    $this->digitalForm = $this->digitalFormsService->getDigitalForm($nid);
    if ($this->digitalForm) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Generates breadcrumbs.
   *
   * @param string $parent
   *   The parent under which the current breadcrumb should be added.
   * @param string $label
   *   The label for the current breadcrumb.
   * @param string|null $url
   *   The url for the current breadcrumb. This can be null or blank,
   *   in which case the template should handle accordingly
   *   (link to current page).
   */
  protected function generateBreadcrumbs($parent, $label, $url = NULL) {
    $breadcrumbTrail = [];

    if ($parent === 'home') {
      $breadcrumbTrail = [
        [
          'label' => 'Home',
          'url' => Url::fromRoute('va_gov_form_builder.home')->toString(),
        ],
      ];
    }

    elseif ($parent === 'layout') {
      if (!$this->digitalForm) {
        return [];
      }

      $layoutUrl = Url::fromRoute('va_gov_form_builder.layout', ['nid' => $this->digitalForm->id()])->toString();
      $breadcrumbTrail = $this->generateBreadcrumbs('home', $this->digitalForm->getTitle(), $layoutUrl);
    }

    $breadcrumbTrail[] = [
      'label' => $label,
      'url' => $url ? $url : '#content',
    ];

    return $breadcrumbTrail;
  }

  /**
   * Returns a render array representing the page with the passed-in content.
   *
   * @param array $pageContent
   *   A render array representing the page content.
   * @param string $subtitle
   *   The subtitle for the page.
   * @param array $breadcrumbs
   *   The breadcrumbs for the page.
   * @param string[] $libraries
   *   Libraries for the page, in addition to the Form Builder general library,
   *   which is added automatically.
   */
  protected function getPage($pageContent, $subtitle, $breadcrumbs = [], $libraries = []) {
    $page = [
      '#type' => 'page',
      'content' => $pageContent,
      '#cache' => [
        // Do not cache Form Builder pages.
        // @todo Make caching more granular/contextual.
        'max-age' => 0,
      ],
      // Add custom data.
      'form_builder_page_data' => [
        'subtitle' => $subtitle,
        'breadcrumbs' => $breadcrumbs,
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
   * @param array $breadcrumbs
   *   The breadcrumbs for the page.
   * @param string[] $libraries
   *   Libraries for the page, in addition to the Form Builder general library,
   *   which is added automatically.
   */
  protected function getFormPage($formName, $subtitle, $breadcrumbs = [], $libraries = []) {
    // @phpstan-ignore-next-line
    $form = $this->drupalFormBuilder->getForm('Drupal\va_gov_form_builder\Form\\' . $formName, $this->digitalForm);

    return $this->getPage($form, $subtitle, $breadcrumbs, $libraries);
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
        'url' => Url::fromRoute('va_gov_form_builder.layout', ['nid' => $digitalForm->id()])->toString(),
      ];
    }

    $pageContent = [
      '#theme' => self::PAGE_CONTENT_THEME_PREFIX . 'home',
      '#build_form_url' => Url::fromRoute('va_gov_form_builder.form_info.create')->toString(),
      '#recent_forms' => $recentForms,
    ];
    $subtitle = 'Select a form';
    $breadcrumbs = [];
    $libraries = ['home'];

    return $this->getPage($pageContent, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Form-info page.
   *
   * @param string|null $nid
   *   The node id, passed in when the page edits an existing node.
   */
  public function formInfo($nid = NULL) {
    $formName = 'FormInfo';
    $subtitle = 'Build a form';
    $libraries = ['form_info'];

    if (!empty($nid)) {
      // This is an edit.
      $nodeFound = $this->loadDigitalForm($nid);
      if (!$nodeFound) {
        throw new NotFoundHttpException();
      }

      $breadcrumbs = $this->generateBreadcrumbs('layout', 'Form info');
    }
    else {
      // This is a form creation.
      $breadcrumbs = $this->generateBreadcrumbs('home', 'Form info');
    }

    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Layout page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   */
  public function layout($nid) {
    $nodeFound = $this->loadDigitalForm($nid);
    if (!$nodeFound) {
      throw new NotFoundHttpException();
    }

    $pageContent = [
      '#theme' => self::PAGE_CONTENT_THEME_PREFIX . 'layout',
      '#form_info' => [
        'status' => $this->digitalForm->getStepStatus('form_info'),
        'url' => Url::fromRoute('va_gov_form_builder.form_info.edit', ['nid' => $nid])->toString(),
      ],
      '#intro' => [
        'status' => $this->digitalForm->getStepStatus('intro'),
        'url' => '',
      ],
      '#your_personal_info' => [
        'status' => $this->digitalForm->getStepStatus('your_personal_info'),
        'url' => '',
      ],
      '#address_info' => [
        'status' => $this->digitalForm->getStepStatus('address_info'),
        'url' => '',
      ],
      '#contact_info' => [
        'status' => $this->digitalForm->getStepStatus('contact_info'),
        'url' => '',
      ],
      '#additional_steps' => [
        'steps' => array_map(function ($step) {
          return [
            // If an additional step exists, it's complete.
            'type' => $step['type'],
            'title' => $step['fields']['field_title'][0]['value'],
            'status' => 'complete',
            'url' => '',
          ];
        }, $this->digitalForm->getNonStandarddSteps()),
        'add_step' => [
          'url' => '',
        ],
      ],
      '#review_and_sign' => [
        'status' => $this->digitalForm->getStepStatus('review_and_sign'),
        'url' => '',
      ],
      '#confirmation' => [
        'status' => $this->digitalForm->getStepStatus('confirmation'),
        'url' => '',
      ],
      '#view_form' => [
        'url' => '',
      ],
    ];
    $subtitle = $this->digitalForm->getTitle();
    $breadcrumbs = $this->generateBreadcrumbs('home', $this->digitalForm->getTitle());
    $libraries = ['layout'];

    return $this->getPage($pageContent, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Name-and-date-of-birth page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   */
  public function nameAndDob($nid) {
    $formName = 'NameAndDob';
    $subtitle = 'Subtitle Placeholder';

    $nodeFound = $this->loadDigitalForm($nid);
    if (!$nodeFound) {
      throw new NotFoundHttpException();
    }

    return $this->getFormPage($formName, $subtitle);
  }

}
