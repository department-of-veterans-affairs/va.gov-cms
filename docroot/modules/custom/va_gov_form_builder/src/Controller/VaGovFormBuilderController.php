<?php

namespace Drupal\va_gov_form_builder\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\va_gov_form_builder\Enum\CustomSingleQuestionPageType;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderPageBase;
use Drupal\va_gov_form_builder\Form\Base\FormBuilderStepBase;
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
   * The Drupal render service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * The paragraph object representing the page.
   *
   * @var \Drupal\paragraphs\Entity\Paragraph|null
   */
  protected $pageParagraph;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);

    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->drupalFormBuilder = $container->get('form_builder');
    $instance->digitalFormsService = $container->get('va_gov_form_builder.digital_forms_service');
    $instance->session = $container->get('session');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Loads and sets the Digital Form object.
   *
   * @param string $nid
   *   The node id to load.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   If the paragraph is not found, or if the paragraph
   *   does not belong to $this->digitalForm.
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  protected function loadStepParagraph($paragraphId) {
    /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
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
   * Loads and sets the page-paragraph object.
   *
   * @param string $paragraphId
   *   The paragraph id to load.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   If the paragraph is not found, or if the paragraph
   *   does not belong to $this->stepParagraph.
   */
  protected function loadPageParagraph($paragraphId) {
    $paragraph = $this->entityTypeManager->getStorage('paragraph')->load($paragraphId);
    if (!$paragraph) {
      throw new NotFoundHttpException();
    }

    // Ensure the page paragraph belongs to the step paragraph.
    if (!$this->stepParagraph) {
      throw new NotFoundHttpException();
    }
    $parentId = $paragraph->get('parent_id') && is_object($paragraph->get('parent_id'))
      ? $paragraph->get('parent_id')->value
      : '';

    if ($parentId !== $this->stepParagraph->id()) {
      throw new NotFoundHttpException();
    }

    $this->pageParagraph = $paragraph;
  }

  /**
   * Determines and returns the type of component(s) on a page paragraph.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *   The paragraph object representing the page.
   *
   * @return \Drupal\va_gov_form_builder\Enum\CustomSingleQuestionPageType
   *   The type of component(s) on the page paragraph.
   *
   * @throws \LogicException
   *   If the paragraph doesn't have the expected field,
   *   if the paragraph doesn't have components,
   *   or if the component found is not an expected type.
   */
  protected static function getPageComponentType($paragraph) {
    if (!$paragraph->hasField('field_digital_form_components')) {
      throw new \LogicException('The paragraph does not have the field "field_digital_form_components".');
    }

    $pageComponents = $paragraph
      ->get('field_digital_form_components')
      ->referencedEntities();

    if (count($pageComponents) < 1) {
      throw new \LogicException('No components found on the page paragraph.');
    }

    $firstComponentBundle = $pageComponents[0]->bundle();

    // Date or Date range.
    if ($firstComponentBundle === 'digital_form_date_component') {
      if (isset($pageComponents[1])) {
        $secondComponentBundle = $pageComponents[1]->bundle();

        if ($secondComponentBundle === 'digital_form_date_component') {
          return CustomSingleQuestionPageType::DateRange;
        }
      }

      return customSingleQuestionPageType::SingleDate;
    }

    // Radio, Checkbox, Text input, Text area.
    $map = [
      'digital_form_radio_button' => CustomSingleQuestionPageType::Radio,
      'digital_form_checkbox' => CustomSingleQuestionPageType::Checkbox,
      'digital_form_text_input' => CustomSingleQuestionPageType::TextInput,
      'digital_form_text_area' => CustomSingleQuestionPageType::TextArea,
    ];
    if (isset($map[$firstComponentBundle])) {
      return $map[$firstComponentBundle];
    }

    // Otherwise, it's an error.
    throw new \LogicException('The page component type cannot be determined.');
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
      'intro',
      'name_and_dob',
      'identification_info',
      'address_info',
      'contact_info',
      'step.step_label.create',
      'step.step_style',
      'step.single_question.custom_or_predefined',
      'step.repeating_set.custom_or_predefined',
      'review_and_sign',
      'view_form',
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
      'step.home',
      'step.step_label.edit',
      'step.question.custom.kind',
      'step.question.custom.date.type',
      'step.question.custom.text.type',
      'step.question.custom.date.single_date.page_title',
      'step.question.custom.date.date_range.page_title',
      'step.question.custom.choice.type',
      'step.question.custom.choice.radio.page_title',
      'step.question.custom.choice.checkbox.page_title',
      'step.question.custom.text.text_input.page_title',
      'step.question.custom.text.text_area.page_title',
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

    // Pages that relate to a question (page) with a step.
    // Require a nid, stepParagraphId, and pageParagraphId.
    $questionPages = [
      'step.question.page_title',
    ];
    if (in_array($page, $questionPages)) {
      if (!$this->digitalForm) {
        throw new \LogicException('Cannot determine page url because the digital form is not set.');
      }
      if (!$this->stepParagraph) {
        throw new \LogicException('Cannot determine page url because the step paragraph is not set.');
      }
      if (!$this->pageParagraph) {
        throw new \LogicException('Cannot determine page url because the page paragraph is not set.');
      }
      return Url::fromRoute("va_gov_form_builder.{$page}", [
        'nid' => $this->digitalForm->id(),
        'stepParagraphId' => $this->stepParagraph->id(),
        'pageParagraphId' => $this->pageParagraph->id(),
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

    elseif ($parent === 'step.step_label.create') {
      if (!$this->digitalForm) {
        return [];
      }

      $stepLabel = $this->session->get(FormBuilderStepBase::SESSION_KEY) ?? 'Step label';
      $stepLabelUrl = $this->getPageUrl('step.step_label.create');
      $breadcrumbTrail = $this->generateBreadcrumbs(
        'layout',
        $stepLabel,
        $stepLabelUrl
      );
    }

    elseif ($parent === 'step.step_style') {
      if (!$this->digitalForm) {
        return [];
      }

      $stepStyleUrl = $this->getPageUrl('step.step_style');
      $breadcrumbTrail = $this->generateBreadcrumbs(
        'step.step_label.create',
        'Step style',
        $stepStyleUrl
      );
    }

    elseif ($parent === 'step.home') {
      if (!$this->digitalForm || !$this->stepParagraph) {
        return [];
      }

      $stepLayoutUrl = $this->getPageUrl('step.home');
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
        'step.home',
        'Custom or predefined',
        $customOrPredefinedUrl
      );
    }

    elseif ($parent === 'step.question.custom.kind') {
      if (!$this->digitalForm || !$this->stepParagraph) {
        return [];
      }

      $kindUrl = $this->getPageUrl('step.question.custom.kind');
      $breadcrumbTrail = $this->generateBreadcrumbs(
        'step.home',
        'Kind',
        $kindUrl
      );
    }

    elseif ($parent === 'step.question.custom.date.type') {
      if (!$this->digitalForm || !$this->stepParagraph) {
        return [];
      }

      $dateTypeUrl = $this->getPageUrl('step.question.custom.date.type');
      $breadcrumbTrail = $this->generateBreadcrumbs(
        'step.question.custom.kind',
        'Date type',
        $dateTypeUrl
      );
    }

    elseif ($parent === 'step.question.custom.text.type') {
      if (!$this->digitalForm || !$this->stepParagraph) {
        return [];
      }

      $textTypeUrl = $this->getPageUrl('step.question.custom.text.type');
      $breadcrumbTrail = $this->generateBreadcrumbs(
        'step.question.custom.kind',
        'Text type',
        $textTypeUrl
      );
    }
    elseif ($parent === 'step.question.custom.choice.type') {
      $choiceTypeUrl = $this->getPageUrl('step.question.custom.choice.type');
      $breadcrumbTrail = $this->generateBreadcrumbs(
        'step.question.custom.kind',
        'Choice type',
        $choiceTypeUrl
      );
    }
    elseif ($parent === 'step.question.custom.choice.radio.page_title') {
      if (!$this->digitalForm || !$this->stepParagraph) {
        return [];
      }
      $pageTitleUrl = $this->getPageUrl('step.question.custom.choice.radio.page_title');
      $breadcrumbTrail = $this->generateBreadcrumbs(
        'step.question.custom.choice.type',
        '',
        $pageTitleUrl
      );
    }
    elseif ($parent === 'step.question.custom.choice.checkbox.page_title') {
      if (!$this->digitalForm || !$this->stepParagraph) {
        return [];
      }
      $pageTitleUrl = $this->getPageUrl('step.question.custom.choice.checkbox.page_title');
      $breadcrumbTrail = $this->generateBreadcrumbs(
        'step.question.custom.choice.type',
        '',
        $pageTitleUrl
      );
    }
    elseif ($parent === 'step.question.page_title') {
      if (!$this->digitalForm || !$this->stepParagraph || !$this->pageParagraph) {
        return [];
      }

      $pageTitleUrl = $this->getPageUrl('step.question.page_title');
      $breadcrumbTrail = $this->generateBreadcrumbs(
        'step.home',
        $this->pageParagraph->get('field_title')->value,
        $pageTitleUrl
      );
    }

    elseif ($parent === 'step.question.custom.date.single_date.page_title') {
      if (!$this->digitalForm || !$this->stepParagraph) {
        return [];
      }

      $pageTitleUrl = $this->getPageUrl('step.question.custom.date.single_date.page_title');
      $breadcrumbTrail = $this->generateBreadcrumbs(
        'step.question.custom.date.type',
        '',
        $pageTitleUrl
      );
    }

    elseif ($parent === 'step.question.custom.date.date_range.page_title') {
      if (!$this->digitalForm || !$this->stepParagraph) {
        return [];
      }

      $pageTitleUrl = $this->getPageUrl('step.question.custom.date.date_range.page_title');
      $breadcrumbTrail = $this->generateBreadcrumbs(
        'step.question.custom.date.type',
        '',
        $pageTitleUrl
      );
    }

    elseif ($parent === 'step.question.custom.text.text_input.page_title') {
      if (!$this->digitalForm || !$this->stepParagraph) {
        return [];
      }
      $pageTitleUrl = $this->getPageUrl('step.question.custom.text.text_input.page_title');
      $breadcrumbTrail = $this->generateBreadcrumbs(
        'step.question.custom.text.type',
        '',
        $pageTitleUrl
      );
    }

    elseif ($parent === 'step.question.custom.text.text_area.page_title') {
      if (!$this->digitalForm || !$this->stepParagraph) {
        return [];
      }
      $pageTitleUrl = $this->getPageUrl('step.question.custom.text.text_area.page_title');
      $breadcrumbTrail = $this->generateBreadcrumbs(
        'step.question.custom.text.type',
        '',
        $pageTitleUrl
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
      $this->pageParagraph,
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
        'url' => $this->getPageUrl('intro'),
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
        'messages' => [
          '#type' => 'status_messages',
        ],
        'steps' => array_map(function ($step) {
          $stepParagraphId = $step['fields']['id'][0]['value'];
          return [
            'type' => $step['type'],
            'title' => $step['fields']['field_title'][0]['value'],
            'status' => $this->digitalForm->getStepStatus('custom', $step['paragraph']),
            'url' => Url::fromRoute('va_gov_form_builder.step.home', [
              'nid' => $this->digitalForm->id(),
              'stepParagraphId' => $stepParagraphId,
            ])->toString(),
            'actions' => $this->getParagraphActions($step['paragraph']),
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
      '#view_form' => [
        'url' => $this->getPageUrl('view_form'),
      ],
    ];

    $subtitle = $this->digitalForm->getTitle();
    $breadcrumbs = $this->generateBreadcrumbs('home', $this->digitalForm->getTitle());
    $libraries = ['layout', 'paragraph_actions'];

    return $this->getPage($pageContent, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Ajax callback for a Custom Step paragraph action.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The DigitalForm node.
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The Paragraph taking the action.
   * @param string $action
   *   Action to take. Route takes care of ensuring only currently available
   *   actions are able to access the route.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response to return.
   *
   * @throws \Exception
   */
  public function customStepAction(NodeInterface $node, ParagraphInterface $paragraph, string $action): AjaxResponse {
    $response = new AjaxResponse();

    // Take the appropriate action on the Paragraph.
    if (method_exists($paragraph, 'executeAction')) {
      $paragraph->executeAction($action);
      $layout = $this->layout($node->id());
      $output = $this->renderer->renderRoot($layout);
      $response->addCommand(new ReplaceCommand('.form-builder-page-container', $output));
    }

    return $response;
  }

  /**
   * Ajax callback for a Page paragraph action.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The DigitalForm node.
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The Paragraph taking the action.
   * @param string $action
   *   Action to take. Route takes care of ensuring only currently available
   *   actions are able to access the route.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response to return.
   *
   * @throws \Exception
   */
  public function pageAction(NodeInterface $node, ParagraphInterface $paragraph, string $action): AjaxResponse {
    $response = new AjaxResponse();

    if (method_exists($paragraph, 'executeAction')) {
      $paragraph->executeAction($action);
      $layout = $this->stepLayout($node->id(), $paragraph->getParentEntity()->id());
      $output = $this->renderer->renderRoot($layout);
      $response->addCommand(new ReplaceCommand('.form-builder-page-container', $output));
    }

    return $response;
  }

  /**
   * Form introduction page.
   *
   * @param string|null $nid
   *   The node id, passed in when the page edits an existing node.
   */
  public function intro($nid = NULL) {
    $this->loadDigitalForm($nid);

    $formName = 'IntroPage';
    $subtitle = $this->digitalForm->getTitle();
    $breadcrumbs = $this->generateBreadcrumbs('layout', 'Introduction page');
    $libraries = [
      'intro',
      'two_column_with_buttons',
    ];

    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
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
        [
          'image' => [
            'alt_text' => 'Name-and-date-of-birth preview',
            'url' => self::IMAGE_DIR . 'name-and-dob.png',
          ],
        ],
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
        [
          'image' => [
            'alt_text' => 'Identification-information preview',
            'url' => self::IMAGE_DIR . 'identification-info.png',
          ],
        ],
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
        [
          'image' => [
            'alt_text' => 'Address-information preview',
            'url' => self::IMAGE_DIR . 'address-info.png',
          ],
        ],
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
        [
          'image' => [
            'alt_text' => 'Contact-information preview',
            'url' => self::IMAGE_DIR . 'contact-info.png',
          ],
        ],
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
   * Employment-history page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string $stepParagraphId
   *   The entity id of the step paragraph.
   */
  protected function employmentHistory($nid, $stepParagraphId) {
    if (empty($this->digitalForm)) {
      $this->loadDigitalForm($nid);
    }
    if (empty($this->stepParagraph)) {
      $this->loadStepParagraph($stepParagraphId);
    }

    $pageContent = [
      '#theme' => self::PAGE_CONTENT_THEME_PREFIX . 'employment_history',
      '#preview' => [
        [
          'title' => 'Page 1: Qualifying question',
          'description' => '
            This question allows the user to exit the list and loop if they have nothing to report.
            This is not editable.
          ',
          'image' => [
            'alt_text' => 'Qualifying-question preview',
            'url' => '/modules/custom/va_gov_form_builder/images/eh-qualifying-question.png',
          ],
        ],
        [
          'title' => 'Page 2: Employer information',
          'description' => '
            If the submitter selects “Yes” from the qualifier, they can start adding Employer
            name and address. This would be on the next page of the form. This is not editable.
          ',
          'image' => [
            'alt_text' => 'Employer-information preview',
            'url' => '/modules/custom/va_gov_form_builder/images/eh-employer-information.png',
          ],
        ],
        [
          'title' => 'Page 3: Employed dates',
          'description' => '
            Continuing to the next page, the submitter would provide the dates that they worked
            for this employer. The name is automatically carried forward. This is not editable.
          ',
          'image' => [
            'alt_text' => 'Employed-dates preview',
            'url' => '/modules/custom/va_gov_form_builder/images/eh-employed-dates.png',
          ],
        ],
        [
          'title' => 'Page 4: Details of this employment and time lost',
          'description' => '
            On a fourth page, the details of this position are filled in by the submitter.
            These questions are not editable.
          ',
          'image' => [
            'alt_text' => 'Employer-information preview',
            'url' => '/modules/custom/va_gov_form_builder/images/eh-lost-time.png',
          ],
        ],
        [
          'title' => 'Page 5: Summary',
          'description' => '
            On the final page of this step, the submitter will see that position, with the
            opportunity to either add another employer or end adding positions.
          ',
        ],
      ],
      '#buttons' => [
        'primary' => [
          'label' => 'Save and continue',
          'url' => $this->getPageUrl('layout'),
        ],
      ],
    ];
    $subtitle = $this->digitalForm->getTitle();
    $breadcrumbs = $this->generateBreadcrumbs('layout', 'Your employers');
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
   *
   * @throws \LogicException
   *   If the step is an invalid type.
   */
  protected function stepLayout($nid, $stepParagraphId) {
    if (empty($this->digitalForm)) {
      $this->loadDigitalForm($nid);
    }
    if (empty($this->stepParagraph)) {
      $this->loadStepParagraph($stepParagraphId);
    }

    $stepLabel = $this->stepParagraph->get('field_title')->value;
    $stepType = $this->stepParagraph->bundle();

    // Single question.
    if ($stepType === 'digital_form_custom_step') {
      $pageEntities = $this->stepParagraph->get('field_digital_form_pages')->referencedEntities();
      $pages = array_map(function ($page) {
        return [
          'id' => $page->id(),
          'title' => $page->get('field_title')->value,
          'actions' => $this->getParagraphActions($page, 'page_action'),
          'url' => "{$this->getPageUrl('step.home')}/question/{$page->id()}",
        ];
      }, $pageEntities);

      $pageContent = [
        '#theme' => self::PAGE_CONTENT_THEME_PREFIX . 'step_layout__single_question',
        '#step_label' => [
          'label' => $stepLabel,
          'url' => $this->getPageUrl('step.step_label.edit'),
        ],
        '#messages' => [
          '#type' => 'status_messages',
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
              'url' => $this->getPageUrl('step.question.custom.kind'),
            ],
          ],
        ],
      ];
    }
    // Repeating set.
    elseif ($stepType === 'digital_form_list_loop') {
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
    else {
      throw new \LogicException('Invalid step type.');
    }

    $subtitle = $this->digitalForm->getTitle();
    $breadcrumbs = $this->generateBreadcrumbs('layout', $stepLabel);
    $libraries = ['single_column_with_buttons', 'step_layout', 'paragraph_actions'];

    return $this->getPage($pageContent, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Home page for a step. Routes step to proper handler.
   *
   * Ex:
   * - Custom single question -> stepLayout.
   * - Custom list & loop -> stepLayout (but not yet supported).
   * - Employment history -> Employment history non-editable pattern.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string $stepParagraphId
   *   The entity id of the step paragraph.
   *
   * @throws \LogicException
   *   If the step type is an unexpected type.
   */
  public function stepHome($nid, $stepParagraphId) {
    $this->loadDigitalForm($nid);
    $this->loadStepParagraph($stepParagraphId);

    $stepType = $this->stepParagraph->bundle();

    // Custom single question.
    // Custom list & loop.
    if ($stepType === 'digital_form_custom_step' || $stepType === 'digital_form_list_loop') {
      return $this->stepLayout($nid, $stepParagraphId);
    }

    // Employment history.
    if ($stepType === 'list_loop_employment_history') {
      return $this->employmentHistory($nid, $stepParagraphId);
    }

    // If step type is anything else, we can't render the page.
    throw new \LogicException('The step type is not supported.');
  }

  /**
   * Builds actions for a paragraph.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph to build actions for.
   * @param string $route_suffix
   *   The suffix for the route.
   *
   * @return array
   *   Array of paragraph actions.
   */
  public function getParagraphActions(ParagraphInterface $paragraph, string $route_suffix = 'custom_step_action'): array {
    // Determine available actions.
    $actions = [];
    if (method_exists($paragraph, 'getActionCollection')) {
      $paragraphActions = $paragraph->getActionCollection();
      /** @var \Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionInterface $paragraphAction */
      foreach ($paragraphActions as $paragraphAction) {
        if ($paragraphAction->checkAccess($paragraph)) {
          $actions[] = [
            'url' => Url::fromRoute('va_gov_form_builder.' . $route_suffix, [
              'node' => $this->digitalForm->id(),
              'paragraph' => $paragraph->id(),
              'action' => $paragraphAction->getKey(),
            ])->toString(),
            'title' => $paragraphAction->getTitle(),
            'action' => $paragraphAction->getKey(),
          ];
        }
      }
    }
    return $actions;
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
      $breadcrumbs = $this->generateBreadcrumbs('step.home', 'Step label');
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
    $stepLabel = $this->session->get(FormBuilderStepBase::SESSION_KEY);
    if (!$stepLabel) {
      return $this->redirect('va_gov_form_builder.step.step_label.create', ['nid' => $nid]);
    }

    $pageContent = [
      '#theme' => self::PAGE_CONTENT_THEME_PREFIX . 'step_style',
      '#step_label' => [
        'label' => $stepLabel,
        'edit_button' => [
          'label' => $this->t('Edit step label'),
          'url' => $this->getPageUrl('step.step_label.create'),
        ],
      ],
      '#preview' => [
        'single_question' => [
          'alt_text' => 'Single-question preview',
          'url' => self::IMAGE_DIR . 'single-question.png',
        ],
        'repeating_set' => [
          'alt_text' => 'Repeating-set preview',
          'url' => self::IMAGE_DIR . 'repeating-set.png',
        ],
      ],
      '#buttons' => [
        'primary' => [
          'label' => $this->t('Add a single question'),
          'url' => $this->getPageUrl('step.single_question.custom_or_predefined'),
        ],
        'secondary' => [
          [
            'label' => $this->t('Add a repeating set'),
            'url' => $this->getPageUrl('step.repeating_set.custom_or_predefined'),
          ],
        ],
      ],
    ];
    $breadcrumbs = $this->generateBreadcrumbs('step.step_label.create', 'Step style');
    $subtitle = $this->digitalForm->getTitle();
    $libraries = ['single_column_with_buttons', 'step_style'];

    return $this->getPage($pageContent, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Custom-or-predefined selection page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string $stepType
   *   Either `single-question` or `repeating-set`.
   */
  public function customOrPredefinedQuestion($nid, $stepType) {
    $this->loadDigitalForm($nid);

    // This is the third stage in the process
    // of creating a new step. The previously
    // entered step label should be in session storage.
    // If it's not there, we should redirect back to the
    // step-label page.
    $stepLabel = $this->session->get(FormBuilderStepBase::SESSION_KEY);
    if (!$stepLabel) {
      return $this->redirect('va_gov_form_builder.step.step_label.create', ['nid' => $nid]);
    }

    if ($stepType === 'single-question') {
      $formName = 'CustomOrPredefinedSingleQuestion';
    }
    elseif ($stepType === 'repeating-set') {
      $formName = 'CustomOrPredefinedRepeatingSet';
    }
    else {
      throw new NotFoundHttpException();
    }

    $breadcrumbs = $this->generateBreadcrumbs('step.step_style', 'Custom or predefined question');
    $subtitle = $this->digitalForm->getTitle();
    $libraries = [
      'single_column_with_buttons',
      'custom_or_predefined_question',
    ];
    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
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
    $breadcrumbs = $this->generateBreadcrumbs('step.home', 'Response kind');
    $subtitle = $this->digitalForm->getTitle();
    $libraries = [
      'single_column_with_buttons',
      'response_kind',
      'expanded_radio',
      'expanded_radio__help_text_optional_image',
      'repeatable_field_groups',
    ];

    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Date-type page for custom single-question date response.
   */
  public function customSingleQuestionDateType($nid, $stepParagraphId) {
    $this->loadDigitalForm($nid);
    $this->loadStepParagraph($stepParagraphId);

    $formName = 'DateType';
    $breadcrumbs = $this->generateBreadcrumbs('step.question.custom.kind', 'Date type');
    $subtitle = $this->digitalForm->getTitle();
    $libraries = [
      'response_kind',
      'single_column_with_buttons',
      'expanded_radio',
      'expanded_radio__help_text_optional_image',
    ];

    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Text-type page for custom single-question text response.
   */
  public function customSingleQuestionTextType($nid, $stepParagraphId) {
    $this->loadDigitalForm($nid);
    $this->loadStepParagraph($stepParagraphId);

    $formName = 'TextType';
    $breadcrumbs = $this->generateBreadcrumbs('step.question.custom.kind', 'Text type');
    $subtitle = $this->digitalForm->getTitle();
    $libraries = [
      'response_kind',
      'single_column_with_buttons',
      'expanded_radio',
      'expanded_radio__help_text_optional_image',
    ];

    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Custom single-question page title.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string $stepParagraphId
   *   The entity id of the step paragraph.
   * @param string|null $pageParagraphId
   *   The entity id of the page paragraph.
   * @param \Drupal\va_gov_form_builder\Enum\CustomSingleQuestionPageType|null $pageComponentType
   *   The type of component(s) on the page.
   *   Ex: 'date.single_date', 'choice.radio'.
   */
  public function customSingleQuestionPageTitle($nid, $stepParagraphId, $pageParagraphId = NULL, $pageComponentType = NULL) {
    $this->loadDigitalForm($nid);
    $this->loadStepParagraph($stepParagraphId);

    $formName = 'CustomSingleQuestionPageTitle';
    $subtitle = $this->digitalForm->getTitle();
    $libraries = [
      'single_column_with_buttons',
      'custom_single_question_page_title',
    ];

    if (!empty($pageParagraphId)) {
      // This is a page edit.
      $this->loadPageParagraph($pageParagraphId);

      try {
        $pageComponentType = self::getPageComponentType($this->pageParagraph);
      }
      catch (\Exception $e) {
        // This is technically not a page-not-found
        // error state, but we don't have a better way to
        // handle this currently.
        // @todo Implement better way to handle general error states.
        throw new NotFoundHttpException();
      }

      // Set the breadcrumbs based on the page component type.
      $breadcrumbs = match($pageComponentType) {
        CustomSingleQuestionPageType::SingleDate =>
          $this->generateBreadcrumbs('step.home', 'Date question'),
        CustomSingleQuestionPageType::DateRange =>
          $this->generateBreadcrumbs('step.home', 'Date-range question'),
        CustomSingleQuestionPageType::Radio =>
          $this->generateBreadcrumbs('step.home', 'Radio-button question'),
        CustomSingleQuestionPageType::Checkbox =>
          $this->generateBreadcrumbs('step.home', 'Checkbox question'),
        CustomSingleQuestionPageType::TextInput =>
          $this->generateBreadcrumbs('step.home', 'Text-input question'),
        CustomSingleQuestionPageType::TextArea =>
          $this->generateBreadcrumbs('step.home', 'Text-area question'),
      };
    }
    else {
      // This is page creation.
      $pageComponentType = CustomSingleQuestionPageType::from($pageComponentType);
      switch ($pageComponentType) {
        case CustomSingleQuestionPageType::SingleDate:
          $breadcrumbs = $this->generateBreadcrumbs('step.question.custom.date.type', 'Date question');
          break;

        case CustomSingleQuestionPageType::DateRange:
          $breadcrumbs = $this->generateBreadcrumbs('step.question.custom.date.type', 'Date-range question');
          break;

        case CustomSingleQuestionPageType::Radio:
          $breadcrumbs = $this->generateBreadcrumbs('step.question.custom.choice.type', 'Radio question');
          break;

        case CustomSingleQuestionPageType::Checkbox:
          $breadcrumbs = $this->generateBreadcrumbs('step.question.custom.choice.type', 'Checkbox question');
          break;

        case CustomSingleQuestionPageType::TextInput:
          $breadcrumbs = $this->generateBreadcrumbs('step.question.custom.text.type', 'Text-input question');
          break;

        case CustomSingleQuestionPageType::TextArea:
          $breadcrumbs = $this->generateBreadcrumbs('step.question.custom.text.type', 'Text-area question');
          break;

        default:
          throw new NotFoundHttpException();
      }
    }

    // @phpstan-ignore-next-line
    $form = $this->drupalFormBuilder->getForm(
      'Drupal\va_gov_form_builder\Form\\' . $formName,
      $this->digitalForm,
      $this->stepParagraph,
      $this->pageParagraph,
      $pageComponentType
    );
    return $this->getPage($form, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Custom single-question "home" page.
   *
   * This route just redirects to the page-title page.
   * This path would be the "page-layout" path, or the page "home"
   * but since we do not have a page-layout page, the user
   * is automatically redirected to the page-title page
   * for the form page in question.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string $stepParagraphId
   *   The entity id of the step paragraph.
   * @param string $pageParagraphId
   *   The entity id of the page paragraph.
   */
  public function customSingleQuestionPage($nid, $stepParagraphId, $pageParagraphId) {
    return $this->redirect('va_gov_form_builder.step.question.page_title', [
      'nid' => $nid,
      'stepParagraphId' => $stepParagraphId,
      'pageParagraphId' => $pageParagraphId,
    ]);
  }

  /**
   * Custom single-question single-date response page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string $stepParagraphId
   *   The entity id of the step paragraph.
   * @param string|null $pageParagraphId
   *   The entity id of the page paragraph.
   */
  public function customSingleQuestionSingleDateResponse($nid, $stepParagraphId, $pageParagraphId = NULL) {
    if (empty($this->digitalForm)) {
      $this->loadDigitalForm($nid);
    }
    if (empty($this->stepParagraph)) {
      $this->loadStepParagraph($stepParagraphId);
    }

    if (!empty($pageParagraphId)) {
      // This is a page edit.
      if (empty($this->pageParagraph)) {
        $this->loadPageParagraph($pageParagraphId);
      }

      $breadcrumbs = $this->generateBreadcrumbs('step.question.page_title', 'Date response');
    }
    else {
      // This is the second stage in the process
      // of creating a new page. The previously
      // entered page title and body should be in
      // session storage. The page title is required.
      // If it is not there, we should redirect back
      // to the page-title page.
      $sessionData = $this->session->get(FormBuilderPageBase::SESSION_KEY);
      $pageTitle = $sessionData['title'] ?? NULL;
      if (!$pageTitle) {
        return $this->redirect(
          'va_gov_form_builder.step.question.custom.date.single_date.page_title',
          [
            'nid' => $nid,
            'stepParagraphId' => $stepParagraphId,
          ],
        );
      }

      // This is page creation.
      $breadcrumbs = $this->generateBreadcrumbs('step.question.custom.date.single_date.page_title', 'Date response');
    }

    // Override the page title with "Date question".
    $breadcrumbs[count($breadcrumbs) - 2]['label'] = 'Date question';

    $formName = 'CustomSingleQuestionSingleDateResponse';
    $subtitle = $this->digitalForm->getTitle();
    $libraries = [
      'two_column_with_buttons',
      'expanded_radio',
      'custom_single_question_response',
      'repeatable_field_groups',
    ];

    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Custom single-question date-range response page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string $stepParagraphId
   *   The entity id of the step paragraph.
   * @param string|null $pageParagraphId
   *   The entity id of the page paragraph.
   */
  public function customSingleQuestionDateRangeResponse($nid, $stepParagraphId, $pageParagraphId = NULL) {
    if (empty($this->digitalForm)) {
      $this->loadDigitalForm($nid);
    }
    if (empty($this->stepParagraph)) {
      $this->loadStepParagraph($stepParagraphId);
    }

    if (!empty($pageParagraphId)) {
      // This is a page edit.
      if (empty($this->pageParagraph)) {
        $this->loadPageParagraph($pageParagraphId);
      }

      $breadcrumbs = $this->generateBreadcrumbs('step.question.page_title', 'Date-range response');
    }
    else {
      // This is the second stage in the process
      // of creating a new page. The previously
      // entered page title and body should be in
      // session storage. The page title is required.
      // If it is not there, we should redirect back
      // to the page-title page.
      $sessionData = $this->session->get(FormBuilderPageBase::SESSION_KEY);
      $pageTitle = $sessionData['title'] ?? NULL;
      if (!$pageTitle) {
        return $this->redirect(
          'va_gov_form_builder.step.question.custom.date.date_range.page_title',
          [
            'nid' => $nid,
            'stepParagraphId' => $stepParagraphId,
          ],
        );
      }

      // This is page creation.
      $breadcrumbs = $this->generateBreadcrumbs(
        'step.question.custom.date.date_range.page_title',
        'Date-range response'
      );
    }

    // Override the page title with "Date question".
    $breadcrumbs[count($breadcrumbs) - 2]['label'] = 'Date-range question';

    $formName = 'CustomSingleQuestionDateRangeResponse';
    $subtitle = $this->digitalForm->getTitle();
    $libraries = [
      'two_column_with_buttons',
      'expanded_radio',
      'custom_single_question_response',
      'repeatable_field_groups',
    ];

    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Custom single-question text-input response page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string $stepParagraphId
   *   The entity id of the step paragraph.
   * @param string|null $pageParagraphId
   *   The entity id of the page paragraph.
   */
  public function customSingleQuestionTextInputResponse($nid, $stepParagraphId, $pageParagraphId = NULL) {
    if (empty($this->digitalForm)) {
      $this->loadDigitalForm($nid);
    }
    if (empty($this->stepParagraph)) {
      $this->loadStepParagraph($stepParagraphId);
    }

    if (!empty($pageParagraphId)) {
      // This is a page edit.
      if (empty($this->pageParagraph)) {
        $this->loadPageParagraph($pageParagraphId);
      }

      $breadcrumbs = $this->generateBreadcrumbs('step.question.page_title', 'Text-input response');
    }
    else {
      // This is the second stage in the process
      // of creating a new page. The previously
      // entered page title and body should be in
      // session storage. The page title is required.
      // If it is not there, we should redirect back
      // to the page-title page.
      $sessionData = $this->session->get(FormBuilderPageBase::SESSION_KEY);
      $pageTitle = $sessionData['title'] ?? NULL;
      if (!$pageTitle) {
        return $this->redirect(
          'va_gov_form_builder.step.question.custom.text.text_input.page_title',
          [
            'nid' => $nid,
            'stepParagraphId' => $stepParagraphId,
          ],
        );
      }

      // This is page creation.
      $breadcrumbs = $this->generateBreadcrumbs(
        'step.question.custom.text.text_input.page_title',
        'Text-input response'
      );
    }

    // Override the page title with "Date question".
    $breadcrumbs[count($breadcrumbs) - 2]['label'] = 'Text-input question';

    $formName = 'CustomSingleQuestionTextInputResponse';
    $subtitle = $this->digitalForm->getTitle();
    $libraries = [
      'two_column_with_buttons',
      'expanded_radio',
      'custom_single_question_response',
      'repeatable_field_groups',
    ];

    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Custom single-question text-area response page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string $stepParagraphId
   *   The entity id of the step paragraph.
   * @param string|null $pageParagraphId
   *   The entity id of the page paragraph.
   */
  public function customSingleQuestionTextAreaResponse($nid, $stepParagraphId, $pageParagraphId = NULL) {
    if (empty($this->digitalForm)) {
      $this->loadDigitalForm($nid);
    }
    if (empty($this->stepParagraph)) {
      $this->loadStepParagraph($stepParagraphId);
    }

    if (!empty($pageParagraphId)) {
      // This is a page edit.
      if (empty($this->pageParagraph)) {
        $this->loadPageParagraph($pageParagraphId);
      }

      $breadcrumbs = $this->generateBreadcrumbs('step.question.page_title', 'Text-area response');
    }
    else {
      // This is the second stage in the process
      // of creating a new page. The previously
      // entered page title and body should be in
      // session storage. The page title is required.
      // If it is not there, we should redirect back
      // to the page-title page.
      $sessionData = $this->session->get(FormBuilderPageBase::SESSION_KEY);
      $pageTitle = $sessionData['title'] ?? NULL;
      if (!$pageTitle) {
        return $this->redirect(
          'va_gov_form_builder.step.question.custom.text.text_area.page_title',
          [
            'nid' => $nid,
            'stepParagraphId' => $stepParagraphId,
          ],
        );
      }

      // This is page creation.
      $breadcrumbs = $this->generateBreadcrumbs(
        'step.question.custom.text.text_area.page_title',
        'Text-area response'
      );
    }

    // Override the page title with "Date question".
    $breadcrumbs[count($breadcrumbs) - 2]['label'] = 'Text-area question';

    $formName = 'CustomSingleQuestionTextAreaResponse';
    $subtitle = $this->digitalForm->getTitle();
    $libraries = [
      'two_column_with_buttons',
      'expanded_radio',
      'custom_single_question_response',
      'repeatable_field_groups',
    ];

    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Custom single-question response page.
   *
   * This is a catch-all method for handling
   * all custom single-question response pages
   * in edit mode. Once we determine which
   * component type the page is, we call the
   * appropriate method to handle it, and we
   * pass in the $pageParagraphId.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string $stepParagraphId
   *   The entity id of the step paragraph.
   * @param string $pageParagraphId
   *   The entity id of the page paragraph.
   */
  public function customSingleQuestionResponse($nid, $stepParagraphId, $pageParagraphId) {
    $this->loadDigitalForm($nid);
    $this->loadStepParagraph($stepParagraphId);
    $this->loadPageParagraph($pageParagraphId);

    // Call appropriate method based on paragraph component type.
    try {
      $pageComponentType = self::getPageComponentType($this->pageParagraph);
    }
    catch (\Exception $e) {
      // This is technically not a page-not-found
      // error state, but we don't have a better way to
      // handle this currently.
      // @todo Implement better way to handle general error states.
      throw new NotFoundHttpException();
    }

    switch ($pageComponentType) {
      case CustomSingleQuestionPageType::SingleDate:
        return $this->customSingleQuestionSingleDateResponse($nid, $stepParagraphId, $pageParagraphId);

      case CustomSingleQuestionPageType::DateRange:
        return $this->customSingleQuestionDateRangeResponse($nid, $stepParagraphId, $pageParagraphId);

      case CustomSingleQuestionPageType::Radio:
        return $this->customSingleQuestionRadioResponse($nid, $stepParagraphId, $pageParagraphId);

      case CustomSingleQuestionPageType::Checkbox:
        return $this->customSingleQuestionCheckboxResponse($nid, $stepParagraphId, $pageParagraphId);

      case CustomSingleQuestionPageType::TextInput:
        return $this->customSingleQuestionTextInputResponse($nid, $stepParagraphId, $pageParagraphId);

      case CustomSingleQuestionPageType::TextArea:
        return $this->customSingleQuestionTextAreaResponse($nid, $stepParagraphId, $pageParagraphId);

    }
  }

  /**
   * Custom single-question checkbox response page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string $stepParagraphId
   *   The entity id of the step paragraph.
   * @param string|null $pageParagraphId
   *   The entity id of the page paragraph.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect or the page response render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function customSingleQuestionCheckboxResponse($nid, $stepParagraphId, $pageParagraphId = NULL) {
    if (empty($this->digitalForm)) {
      $this->loadDigitalForm($nid);
    }
    if (empty($this->stepParagraph)) {
      $this->loadStepParagraph($stepParagraphId);
    }

    if (!empty($pageParagraphId)) {
      // This is a page edit.
      if (empty($this->pageParagraph)) {
        $this->loadPageParagraph($pageParagraphId);
      }

      $breadcrumbs = $this->generateBreadcrumbs('step.question.page_title', 'Checkbox response');
    }
    else {
      // This is the second stage in the process
      // of creating a new page. The previously
      // entered page title and body should be in
      // session storage. The page title is required.
      // If it is not there, we should redirect back
      // to the page-title page.
      $sessionData = $this->session->get(FormBuilderPageBase::SESSION_KEY);
      $pageTitle = $sessionData['title'] ?? NULL;
      if (!$pageTitle) {
        return $this->redirect(
          'va_gov_form_builder.step.question.custom.choice.checkbox.page_title',
          [
            'nid' => $nid,
            'stepParagraphId' => $stepParagraphId,
          ],
        );
      }

      // This is page creation.
      $breadcrumbs = $this->generateBreadcrumbs('step.question.custom.choice.checkbox.page_title', 'Checkbox response');
    }

    // Override the page title with "Checkbox question".
    $breadcrumbs[count($breadcrumbs) - 2]['label'] = 'Checkbox question';

    $formName = 'CustomSingleQuestionCheckboxResponse';
    $subtitle = $this->digitalForm->getTitle();
    $libraries = [
      'two_column_with_buttons',
      'expanded_radio',
      'custom_single_question_response',
      'repeatable_field_groups',
    ];

    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Custom single-question radio response page.
   *
   * @param string $nid
   *   The node id of the Digital Form.
   * @param string $stepParagraphId
   *   The entity id of the step paragraph.
   * @param string|null $pageParagraphId
   *   The entity id of the page paragraph.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect or the page response render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function customSingleQuestionRadioResponse($nid, $stepParagraphId, $pageParagraphId = NULL) {
    if (empty($this->digitalForm)) {
      $this->loadDigitalForm($nid);
    }
    if (empty($this->stepParagraph)) {
      $this->loadStepParagraph($stepParagraphId);
    }

    if (!empty($pageParagraphId)) {
      // This is a page edit.
      if (empty($this->pageParagraph)) {
        $this->loadPageParagraph($pageParagraphId);
      }

      $breadcrumbs = $this->generateBreadcrumbs('step.question.page_title', 'Radio response');
    }
    else {
      // This is the second stage in the process
      // of creating a new page. The previously
      // entered page title and body should be in
      // session storage. The page title is required.
      // If it is not there, we should redirect back
      // to the page-title page.
      $sessionData = $this->session->get(FormBuilderPageBase::SESSION_KEY);
      $pageTitle = $sessionData['title'] ?? NULL;
      if (!$pageTitle) {
        return $this->redirect(
          'va_gov_form_builder.step.question.custom.choice.radio.page_title',
          [
            'nid' => $nid,
            'stepParagraphId' => $stepParagraphId,
          ],
        );
      }

      // This is page creation.
      $breadcrumbs = $this->generateBreadcrumbs('step.question.custom.choice.radio.page_title', 'Radio response');
    }

    // Override the page title with "Radio question".
    $breadcrumbs[count($breadcrumbs) - 2]['label'] = 'Radio question';

    $formName = 'CustomSingleQuestionRadioResponse';
    $subtitle = $this->digitalForm->getTitle();
    $libraries = [
      'two_column_with_buttons',
      'expanded_radio',
      'custom_single_question_response',
      'repeatable_field_groups',
    ];

    return $this->getFormPage($formName, $subtitle, $breadcrumbs, $libraries);
  }

  /**
   * Custom single question choice type page.
   *
   * @param int $nid
   *   The node id.
   * @param int $stepParagraphId
   *   The step paragraph.
   *
   * @return array
   *   The render array for this choice type.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function customSingleQuestionChoiceType($nid, $stepParagraphId) {
    $this->loadDigitalForm($nid);
    $this->loadStepParagraph($stepParagraphId);

    $formName = 'ChoiceType';
    $breadcrumbs = $this->generateBreadcrumbs('step.question.custom.kind', 'Choice type');
    $subtitle = $this->digitalForm->getTitle();
    $libraries = [
      'response_kind',
      'single_column_with_buttons',
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
        [
          'image' => [
            'alt_text' => 'Statement-of-truth preview',
            'url' => '/modules/custom/va_gov_form_builder/images/statement-of-truth.png',
          ],
        ],
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
