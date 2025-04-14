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
   * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   If the node is not found.
   */
  protected function loadDigitalForm($nid) {
    $this->digitalForm = $this->digitalFormsService->getDigitalForm($nid);
    if (!$this->digitalForm) {
      throw new NotFoundHttpException();
    }
  }

  /**
   * Loads and sets the step-paragraph object.
   *
   * @param string $paragraphId
   *   The paragraph id to load.
   *
   * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   If the pargraph is not found, or if the paragraph
   *   does not belong to $this->digitalForm.
   */
  protected function loadStepParagraph($paragraphId) {
    $paragraph = $this->entityTypeManager->getStorage('paragraph')->load($paragraphId);
    if (!$paragraph) {
      throw new NotFoundHttpException();
    }

    // Ensure the paragraph belongs to the node in question.
    if (!$this->digitalForm) {
      throw new NotFoundHttpException();
    }
    $parentId = $paragraph->get('parent_id') && is_object($paragraph->get('parent_id'))
      ? $paragraph->get('parent_id')->value
      : '';

    if ($parentId !== $this->digitalForm->id()) {
      throw new NotFoundHttpException();
    }

    $this->stepParagraph = $paragraph;
  }

  /**
   * Returns the URL for a given page.
   *
   * This method is effectively a wrapper around
   * `Url::fromRoute()`, but abstracts away the
   * addition of passing the entity id's to each call
   * to that function. In this method, if the page
   * requires one or more entity id's, those id's
   * are grabbed from the entities stored in state and
   * added to the call to build the url.
   *
   * Ex:
   * 'home' => '/form-builder/home'
   * 'form_info.create' => '/form-builder/form-info'
   * 'layout' => '/form-builder/123456'
   * 'form_info.edit' => '/form-builder/123456/form-info'
   * 'step.step_label.edit' => '/form-builder/123456/step/456789/step-label'
   *
   * @param string $page
   *   The page name. This should match the routing name.
   *
   * @return string
   *   Returns the url for the page. Throws an exception if
   *   a route for the page is not configured or if
   *   a required entity is not set and is required
   *   for the page to make sense.
   */
  protected function getPageUrl($page) {
    // Pages that do not relate to a specific form.
    $basicPages = ['entry', 'home', 'form_info.create'];
    if (in_array($page, $basicPages)) {
      return Url::fromRoute("va_gov_form_builder.{$page}")->toString();
    }

    // Pages that relate to a specific form
    // (but not a step or page within that form).
    // Require only a nid.
    $formPages = [
      'layout',
      'form_info.edit',
      'name_and_dob', 'identification_info', 'address_info', 'contact_info',
      'step.step_label.create', 'step.step_style',
      'review_and_sign', 'view_form',
    ];
    if (in_array($page, $formPages)) {
      if (!$this->digitalForm) {
        throw new \LogicException('Cannot determine page url because the digital form is not set.');
      }
      return Url::fromRoute("va_gov_form_builder.{$page}", [
        'nid' => $this->digitalForm->id(),
      ])->toString();
    }

    // Pages that relate to a step within a form.
    // Require a nid and stepParagraphId.
    $stepPages = [
      'step.layout',
      'step.step_label.edit',
      'step.question.custom_or_predefined',
      'step.question.custom.kind',
    ];
    if (in_array($page, $stepPages)) {
      if (!$this->digitalForm) {
        throw new \LogicException('Cannot determine page url because the digital form is not set.');
      }
      if (!$this->stepParagraph) {
        throw new \LogicException('Cannot determine page url because the step paragraph is not set.');
      }
      return Url::fromRoute("va_gov_form_builder.{$page}", [
        'nid' => $this->digitalForm->id(),
        'stepParagraphId' => $this->stepParagraph->id(),
      ])->toString();
    }

    return '';
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
  protected function getLaunchViewLink() {
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

    elseif ($parent === 'step.layout') {
      if (!$this->digitalForm || !$this->stepParagraph) {
        return [];
      }

      $stepLayoutUrl = $this->getPageUrl('step.layout');
      $breadcrumbTrail = $this->generateBreadcrumbs(
        'layout',
        $this->stepParagraph->get('field_title')->value,
        $stepLayoutUrl
      );
    }

    elseif ($parent === 'step.question.custom_or_predefined') {
      if (!$this->digitalForm || !$this->stepParagraph) {
        return [];
      }

      $customOrPredefinedUrl = $this->getPageUrl('step.question.custom_or_predefined');
      $breadcrumbTrail = $this->generateBreadcrumbs(
        'step.layout',
        'Custom or predefined',
        $customOrPredefinedUrl
      );
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
      $this->loadDigitalForm($nid);

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
    $this->loadDigitalForm($nid);

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
          $stepParagraphId = $step['fields']['id'][0]['value'];
          return [
            'type' => $step['type'],
            'title' => $step['fields']['field_title'][0]['value'],
            'status' => $this->digitalForm->getStepStatus('custom', $step['paragraph']),
            'url' => Url::fromRoute('va_gov_form_builder.step.layout', [
              'nid' => $this->digitalForm->id(),
              'stepParagraphId' => $stepParagraphId,
            ])->toString(),
          ];
        }, $this->digitalForm->getNonStandarddSteps()),
        'add_step' => [
          'url' => $this->getPageUrl('step.step_label.create'),
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
    $this->loadDigitalForm($nid);

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
    $this->loadDigitalForm($nid);

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
    $this->loadDigitalForm($nid);

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
    $this->loadDigitalForm($nid);

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
   * Step-layout page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string $stepParagraphId
   *   The entity id of the step paragraph.
   */
  public function stepLayout($nid, $stepParagraphId) {
    $this->loadDigitalForm($nid);
    $this->loadStepParagraph($stepParagraphId);

    $stepLabel = $this->stepParagraph->get('field_title')->value;
    $stepType = $this->stepParagraph->bundle();

    // Single question.
    if ($stepType === 'digital_form_custom_step') {
      $pageEntities = $this->stepParagraph->get('field_digital_form_pages')->referencedEntities();
      $pages = array_map(function ($page) {
        return [
          'id' => $page->id(),
          'title' => $page->get('field_title')->value,
          'url' => '',
        ];
      }, $pageEntities);

      $pageContent = [
        '#theme' => self::PAGE_CONTENT_THEME_PREFIX . 'step_layout__single_question',
        '#step_label' => [
          'label' => $stepLabel,
          'url' => $this->getPageUrl('step.step_label.edit'),
        ],
        '#pages' => $pages,
        '#buttons' => [
          'primary' => [
            'label' => 'Return to steps',
            'url' => $this->getPageUrl('layout'),
          ],
          'secondary' => [
            [
              'label' => 'Add question',
              'url' => $this->getPageUrl('step.question.custom_or_predefined'),
            ],
          ],
        ],
      ];
    }
    // Repeating set.
    else {
      $pageContent = [
        '#theme' => self::PAGE_CONTENT_THEME_PREFIX . 'step_layout__repeating_set',
        '#pages' => [],
        '#buttons' => [
          'primary' => [
            'label' => 'Return to steps',
            'url' => $this->getPageUrl('layout'),
          ],
        ],
      ];
    }

    $subtitle = $this->digitalForm->getTitle();
    $breadcrumbs = $this->generateBreadcrumbs('layout', $stepLabel);
    $libraries = ['single_column_with_buttons', 'step_layout', 'paragraph_sort_and_delete'];

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
    $this->loadDigitalForm($nid);

    if ($stepParagraphId) {
      $this->loadStepParagraph($stepParagraphId);
      $breadcrumbs = $this->generateBreadcrumbs('step.layout', 'Step label');
    }
    else {
      $breadcrumbs = $this->generateBreadcrumbs('layout', 'Step label');
    }

    $formName = 'StepLabel';
    $subtitle = $this->digitalForm->getTitle();
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
    $this->loadDigitalForm($nid);

    // This is the second stage in the process
    // of creating a new step. The previously
    // entered step label should be in session storage.
    // If it's not there, we should redirect back to the
    // step-label page.
    $stepLabel = $this->session->get('form_builder:add_step:step_label');
    if (!$stepLabel) {
      return $this->redirect('va_gov_form_builder.step.step_label.create', ['nid' => $nid]);
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
        'url' => $this->getPageUrl('step.step_label.create'),
      ],
    ]);

    $formName = 'StepStyle';
    $subtitle = $this->digitalForm->getTitle();
    $libraries = ['single_column_with_buttons', 'step_style'];

    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Custom-or-predefined-question page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string $stepParagraphId
   *   The entity id of the step paragraph.
   */
  public function customOrPredefinedQuestion($nid, $stepParagraphId) {
    $this->loadDigitalForm($nid);
    $this->loadStepParagraph($stepParagraphId);

    $stepType = $this->stepParagraph->bundle();

    // Eventually, '#predefined_questions' will
    // be a list conditionally populated
    // based on `$stepType`. For now, we only support
    // custom questions.
    $predefinedQuestions = [];

    $pageContent = [
      '#predefined_questions' => $predefinedQuestions,
      '#buttons' => [
        'primary' => [
          'label' => 'Customize',
          'url' => '',
        ],
      ],
    ];

    if ($stepType === 'digital_form_custom_step') {
      $pageContent['#theme'] = self::PAGE_CONTENT_THEME_PREFIX . 'custom_or_predefined_question__single_question';
      $pageContent['#buttons']['primary']['url'] = $this->getPageUrl('step.question.custom.kind');
    }
    else {
      $pageContent['#theme'] = self::PAGE_CONTENT_THEME_PREFIX . 'custom_or_predefined_question__repeating_set';
    }

    $subtitle = $this->digitalForm->getTitle();
    $breadcrumbs = $this->generateBreadcrumbs('step.layout', 'Custom or predefined question');
    $libraries = ['single_column_with_buttons', 'custom_or_predefined_question'];

    return $this->getPage($pageContent, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Response-kind page for custom single-question questions.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string $stepParagraphId
   *   The entity id of the step paragraph.
   */
  public function customSingleQuestionResponseKind($nid, $stepParagraphId) {
    $this->loadDigitalForm($nid);
    $this->loadStepParagraph($stepParagraphId);

    $formName = 'ResponseKind';
    $breadcrumbs = $this->generateBreadcrumbs('step.question.custom_or_predefined', 'Response kind');
    $subtitle = $this->digitalForm->getTitle();
    $libraries = [
      'single_column_with_buttons',
      'response_kind',
      'expanded_radio',
      'expanded_radio__help_text_optional_image',
    ];

    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Review-and-sign page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   */
  public function reviewAndSign($nid) {
    $this->loadDigitalForm($nid);

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
   * View-form page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   */
  public function viewForm($nid) {
    $this->loadDigitalForm($nid);

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
