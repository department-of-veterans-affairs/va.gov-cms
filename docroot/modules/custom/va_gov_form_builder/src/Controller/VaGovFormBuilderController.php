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
   * The Form Builder's image directory.
   */
  const IMAGE_DIR = '/modules/custom/va_gov_form_builder/images/';

  /**
   * The prefix for the page-content theme definitions.
   */
  const PAGE_CONTENT_THEME_PREFIX = 'page_content__va_gov_form_builder__';

  /**
   * The prefix for the page-specific style libraries.
   */
  const LIBRARY_PREFIX = 'va_gov_form_builder/';

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The session service.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

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
   * The paragraph object representing the step.
   *
   * @var \Drupal\paragraphs\Entity\Paragraph|null
   */
  protected $stepParagraph;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);

    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->drupalFormBuilder = $container->get('form_builder');
    $instance->digitalFormsService = $container->get('va_gov_form_builder.digital_forms_service');
    $instance->session = $container->get('session');

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
   * Loads and sets the step-paragraph object.
   *
   * @param string $paragraphId
   *   The paragraph id to load.
   *
   * @return bool
   *   TRUE if successfully loaded. FALSE otherwise.
   */
  protected function loadStepParagraph($paragraphId) {
    $paragraph = $this->entityTypeManager->getStorage('paragraph')->load($paragraphId);
    if (!$paragraph) {
      return FALSE;
    }

    // Ensure the paragraph belongs to the node in question.
    if (!$this->digitalForm) {
      return FALSE;
    }
    $parentId = $paragraph->get('parent_id') && is_object($paragraph->get('parent_id'))
      ? $paragraph->get('parent_id')->value
      : '';

    if ($parentId !== $this->digitalForm->id()) {
      return FALSE;
    }

    $this->stepParagraph = $paragraph;
    return TRUE;
  }

  /**
   * Returns the URL for a given page.
   *
   * This method is effectively a wrapper around
   * `Url::fromRoute()`, but abstracts away the
   * addition of passing the node id to each call to that
   * function. In this method, if the page requires
   * a current digital form node, the node id of the
   * current digital form node is added to the call
   * to build the url.
   *
   * Ex:
   * 'home' => '/form-builder/home'
   * 'form_info.create' => '/form-builder/form-info'
   * 'layout' => '/form-builder/123456'
   * 'form_info.edit' => '/form-builder/123456/form-info'
   *
   * @param string $page
   *   The page name. This should match the routing name.
   *
   * @return string
   *   Returns the url for the page. Throws an exception if
   *   a route for the page is not configured or if
   *   there is no digital form set and one is required
   *   for the page to make sense.
   */
  protected function getPageUrl($page) {
    $nonNodePages = ['home', 'form_info.create'];
    if (in_array($page, $nonNodePages)) {
      return Url::fromRoute("va_gov_form_builder.{$page}")->toString();
    }

    if (!$this->digitalForm) {
      throw new \LogicException('Cannot determine page url because the digital form is not set.');
    }

    return Url::fromRoute("va_gov_form_builder.{$page}", ['nid' => $this->digitalForm->id()])->toString();
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
          'url' => $this->getPageUrl('home'),
        ],
      ];
    }

    elseif ($parent === 'layout') {
      if (!$this->digitalForm) {
        return [];
      }

      $layoutUrl = $this->getPageUrl('layout');
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
          self::LIBRARY_PREFIX . 'form_builder',
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
    // We pass all the possible data points to the form.
    // Unused data points will be ignored by the form.
    //
    // Example:
    // If the form does not expect any paragraphs,
    // the form's constructor will only expect the digitalForm
    // and the additional parameters will safely be ignored.
    //
    // @phpstan-ignore-next-line
    $form = $this->drupalFormBuilder->getForm(
      'Drupal\va_gov_form_builder\Form\\' . $formName,
      $this->digitalForm,
      $this->stepParagraph,
    );

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
      '#build_form_url' => $this->getPageUrl('form_info.create'),
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
        'url' => $this->getPageUrl('form_info.edit'),
      ],
      '#intro' => [
        'status' => $this->digitalForm->getStepStatus('intro'),
        'url' => '',
      ],
      '#your_personal_info' => [
        'status' => $this->digitalForm->getStepStatus('your_personal_info'),
        'url' => $this->getPageUrl('name_and_dob'),
      ],
      '#address_info' => [
        'status' => $this->digitalForm->getStepStatus('address_info'),
        'url' => $this->getPageUrl('address_info'),
      ],
      '#contact_info' => [
        'status' => $this->digitalForm->getStepStatus('contact_info'),
        'url' => $this->getPageUrl('contact_info'),
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
          'url' => $this->getPageUrl('step.add.step_label'),
        ],
      ],
      '#review_and_sign' => [
        'status' => $this->digitalForm->getStepStatus('review_and_sign'),
        'url' => $this->getPageUrl('review_and_sign'),
      ],
      '#confirmation' => [
        'status' => $this->digitalForm->getStepStatus('confirmation'),
        'url' => '',
      ],
      '#view_form' => [
        'url' => $this->getPageUrl('view_form'),
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
    $nodeFound = $this->loadDigitalForm($nid);
    if (!$nodeFound) {
      throw new NotFoundHttpException();
    }

    $pageContent = [
      '#theme' => self::PAGE_CONTENT_THEME_PREFIX . 'name_and_dob',
      '#preview' => [
        'alt_text' => 'Name-and-date-of-birth preview',
        'url' => self::IMAGE_DIR . 'name-and-dob.png',
      ],
      '#buttons' => [
        'primary' => [
          'label' => 'Save and continue',
          'url' => $this->getPageUrl('layout'),
        ],
        'secondary' => [
          [
            'label' => 'Next page',
            'url' => $this->getPageUrl('identification_info'),
          ],
        ],
      ],
    ];

    $subtitle = $this->digitalForm->getTitle();
    $breadcrumbs = $this->generateBreadcrumbs('layout', 'Personal information');
    $libraries = [
      'single_column_with_buttons',
      'non_editable_pattern',
    ];

    return $this->getPage($pageContent, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Identification-info page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   */
  public function identificationInfo($nid) {
    $nodeFound = $this->loadDigitalForm($nid);
    if (!$nodeFound) {
      throw new NotFoundHttpException();
    }

    $pageContent = [
      '#theme' => self::PAGE_CONTENT_THEME_PREFIX . 'identification_info',
      '#preview' => [
        'alt_text' => 'Identification-information preview',
        'url' => self::IMAGE_DIR . 'identification-info.png',
      ],
      '#buttons' => [
        'primary' => [
          'label' => 'Save and continue',
          'url' => $this->getPageUrl('layout'),
        ],
        'secondary' => [
          [
            'label' => 'Previous page',
            'url' => $this->getPageUrl('name_and_dob'),
          ],
        ],
      ],
    ];

    $subtitle = $this->digitalForm->getTitle();
    $breadcrumbs = $this->generateBreadcrumbs('layout', 'Personal information');
    $libraries = [
      'single_column_with_buttons',
      'non_editable_pattern',
    ];

    return $this->getPage($pageContent, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Address-info page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   */
  public function addressInfo($nid) {
    $nodeFound = $this->loadDigitalForm($nid);
    if (!$nodeFound) {
      throw new NotFoundHttpException();
    }

    $pageContent = [
      '#theme' => self::PAGE_CONTENT_THEME_PREFIX . 'address_info',
      '#preview' => [
        'alt_text' => 'Address-information preview',
        'url' => self::IMAGE_DIR . 'address-info.png',
      ],
      '#buttons' => [
        'primary' => [
          'label' => 'Save and continue',
          'url' => $this->getPageUrl('layout'),
        ],
      ],
    ];
    $subtitle = $this->digitalForm->getTitle();
    $breadcrumbs = $this->generateBreadcrumbs('layout', 'Address information');
    $libraries = [
      'single_column_with_buttons',
      'non_editable_pattern',
    ];

    return $this->getPage($pageContent, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Contact-info page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   */
  public function contactInfo($nid) {
    $nodeFound = $this->loadDigitalForm($nid);
    if (!$nodeFound) {
      throw new NotFoundHttpException();
    }

    $pageContent = [
      '#theme' => self::PAGE_CONTENT_THEME_PREFIX . 'contact_info',
      '#preview' => [
        'alt_text' => 'Contact-information preview',
        'url' => self::IMAGE_DIR . 'contact-info.png',
      ],
      '#buttons' => [
        'primary' => [
          'label' => 'Save and continue',
          'url' => $this->getPageUrl('layout'),
        ],
      ],
    ];
    $subtitle = $this->digitalForm->getTitle();
    $breadcrumbs = $this->generateBreadcrumbs('layout', 'Contact information');
    $libraries = [
      'single_column_with_buttons',
      'non_editable_pattern',
    ];

    return $this->getPage($pageContent, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Step-label page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string|null $stepParagraphId
   *   The entity id of the step paragraph.
   */
  public function stepLabel($nid, $stepParagraphId = NULL) {
    $nodeFound = $this->loadDigitalForm($nid);
    if (!$nodeFound) {
      throw new NotFoundHttpException();
    }

    if ($stepParagraphId) {
      $stepParagraphFound = $this->loadStepParagraph($stepParagraphId);
      if (!$stepParagraphFound) {
        throw new NotFoundHttpException();
      }
    }

    $formName = 'StepLabel';
    $subtitle = $this->digitalForm->getTitle();
    $breadcrumbs = $this->generateBreadcrumbs('layout', 'Step label');
    $libraries = ['single_column_with_buttons', 'step_label'];

    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Step-style page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   */
  public function stepStyle($nid) {
    $nodeFound = $this->loadDigitalForm($nid);
    if (!$nodeFound) {
      throw new NotFoundHttpException();
    }

    // This is the second stage in the process
    // of creating a new step. The previously
    // entered step label should be in session storage.
    // If it's not there, we should redirect back to the
    // step-label page.
    $stepLabel = $this->session->get('form_builder:add_step:step_label');
    if (!$stepLabel) {
      return $this->redirect('va_gov_form_builder.step.add.step_label', ['nid' => $nid]);
    }

    // This is a special circumstance of generating breadcrumbs.
    //
    // What we want is this:
    // Home > [Form name] > [Step label] > Step style
    //
    // Typically, we'd accomplish this by:
    // $this->generateBreadcrumbs('step-layout', 'Step style');
    //
    // However, since the step paragraph doesn't actually exist
    // at this point (this is part of the process of creating it),
    // the previously entered step label is stored in session storage.
    // So, we can't do what we'd typically do and, instead, we need
    // to generate the breadcrumb trail without it and then splice
    // it in.
    $breadcrumbs = $this->generateBreadcrumbs('layout', 'Step style');
    array_splice($breadcrumbs, 2, 0, [
      [
        'label' => $stepLabel,
        'url' => $this->getPageUrl('step.add.step_label'),
      ],
    ]);

    $formName = 'StepStyle';
    $subtitle = $this->digitalForm->getTitle();
    $libraries = ['single_column_with_buttons', 'step_style'];

    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Review-and-sign page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   */
  public function reviewAndSign($nid) {
    $nodeFound = $this->loadDigitalForm($nid);
    if (!$nodeFound) {
      throw new NotFoundHttpException();
    }

    $pageContent = [
      '#theme' => self::PAGE_CONTENT_THEME_PREFIX . 'review_and_sign',
      '#preview' => [
        'alt_text' => 'Statement-of-truth preview',
        'url' => '/modules/custom/va_gov_form_builder/images/statement-of-truth.png',
      ],
      '#buttons' => [
        'primary' => [
          'label' => 'Save and continue',
          'url' => $this->getPageUrl('layout'),
        ],
      ],
    ];
    $subtitle = $this->digitalForm->getTitle();
    $breadcrumbs = $this->generateBreadcrumbs('layout', 'Review page');
    $libraries = [
      'single_column_with_buttons',
      'non_editable_pattern',
    ];

    return $this->getPage($pageContent, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Uses field value to construct qualified form application preview link.
   *
   * Likely to be in one of three or four formats:
   *  - https://staging.va.gov/form-application-url
   *  - staging.va.gov/form-application-url
   *  - /form-application-url
   *  - form-application-url
   * This function normalizes those cases to return a valid preview link.
   */
  private function getLaunchViewLink() {
    $applicationUrlField = $this->digitalForm->get('field_form_application_url');

    // Bail early if no field value.
    if ($applicationUrlField->isEmpty()) {
      return '';
    }

    // Parse field value into url parts.
    $url = parse_url($applicationUrlField->getString());

    $scheme = 'https://';
    $previewHost = 'staging.va.gov';

    if (str_starts_with($url['path'], 'staging.va.gov')) {
      return $scheme . $url['path'];
    }
    if (!str_starts_with($url['path'], '/')) {
      return $scheme . $previewHost . '/' . $url['path'];
    }
    return $scheme . $previewHost . $url['path'];
  }

  /**
   * View-form page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   */
  public function viewForm($nid) {
    $nodeFound = $this->loadDigitalForm($nid);
    if (!$nodeFound) {
      throw new NotFoundHttpException();
    }

    $applicationUrl = $this->getLaunchViewLink();
    $isFormViewable = !empty($applicationUrl);
    $viewFormStatus = $isFormViewable ? 'available' : 'unavailable';

    $buttons = $isFormViewable ? [
      'primary' => [
        'label' => 'Launch view',
        'url' => $applicationUrl,
        'target' => '_blank',
      ],
      'secondary' => [
        [
          'label' => 'Return',
          'url' => $this->getPageUrl('layout'),
        ],
      ],
    ] : [
      'primary' => [
        'label' => 'Return',
        'url' => $this->getPageUrl('layout'),
      ],
    ];

    $breadcrumbLabel = $isFormViewable ? 'View form' : 'Form not ready';

    $pageContent = [
      '#theme' => self::PAGE_CONTENT_THEME_PREFIX . 'view_form__' . $viewFormStatus,
      '#buttons' => $buttons,
    ];

    $subtitle = $this->digitalForm->getTitle();
    $breadcrumbs = $this->generateBreadcrumbs('layout', $breadcrumbLabel);
    $libraries = ['single_column_with_buttons'];

    return $this->getPage($pageContent, $subtitle, $breadcrumbs, $libraries);
  }

}
