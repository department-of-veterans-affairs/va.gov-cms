<?php

namespace Drupal\va_gov_form_builder\Form\Base;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\va_gov_form_builder\EntityWrapper\DigitalForm;
use Drupal\va_gov_form_builder\Enum\CustomSingleQuestionPageType;
use Drupal\va_gov_form_builder\Service\DigitalFormsService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Abstract base class for Form Builder page (question) pages.
 */
abstract class FormBuilderPageBase extends FormBuilderBase {

  /**
   * The session key used to store the page title and body.
   *
   * @var string
   */
  const SESSION_KEY = 'form_builder:add_page:page_info';

  /**
   * The DigitalForm object loaded by this form.
   *
   * @var \Drupal\va_gov_form_builder\EntityWrapper\DigitalForm
   */
  protected $digitalForm;

  /**
   * The paragraph object representing the step.
   *
   * @var \Drupal\paragraphs\Entity\Paragraph
   */
  protected $stepParagraph;

  /**
   * The paragraph object representing the page paragraph.
   *
   * This is created or edited by this form.
   *
   * @var \Drupal\paragraphs\Entity\Paragraph|null
   */
  protected $pageParagraph;

  /**
   * The component type of the page.
   *
   * If $pageParagraph is set, this component type refers
   * to the type of component(s) that exist on that paragraph.
   *
   * If $pageParagraph is not set, this component type refers
   * to the path the user is currently on in creating a new page.
   *
   * Ex: `date.single_date`
   *
   * @var \Drupal\va_gov_form_builder\Enum\CustomSingleQuestionPageType
   */
  protected $pageComponentType;

  /**
   * Flag indicating whether this form allows an empty page paragraph.
   *
   * This defaults to TRUE. Most (or all) page-level forms should allow and
   * empty page paragraph because they can all render as part of the
   * page-creation process, in which case the page paragraph is not
   * yet created.
   *
   * @var bool
   */
  protected $allowEmptyPageParagraph;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, DigitalFormsService $digitalFormsService, SessionInterface $session) {
    parent::__construct($entityTypeManager, $digitalFormsService, $session);
    $this->allowEmptyPageParagraph = TRUE;
  }

  /**
   * Sets (creates or updates) a page-paragraph object from the form-state data.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  abstract protected function setPageParagraphFromFormState(FormStateInterface $form_state);

  /**
   * Returns a field value from the page paragraph.
   *
   * If a page paragraph is not set, or `fieldName`
   * does not exist, returns NULL. This is primarily
   * used to populate forms with default values when the
   * form edits an existing page paragraph.
   *
   * @param string $fieldName
   *   The name of the field whose value should be fetched.
   */
  protected function getPageParagraphFieldValue($fieldName) {
    if (empty($this->pageParagraph)) {
      return NULL;
    }

    try {
      return $this->pageParagraph->get($fieldName)->value;
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param \Drupal\va_gov_form_builder\EntityWrapper\DigitalForm|null $digitalForm
   *   The Digital Form object.
   * @param \Drupal\paragraphs\Entity\Paragraph|null $stepParagraph
   *   The paragraph object representing the step.
   * @param \Drupal\paragraphs\Entity\Paragraph|null $pageParagraph
   *   The paragraph object representing the page paragraph.
   * @param \Drupal\va_gov_form_builder\Enum\CustomSingleQuestionPageType|null $pageComponentType
   *   The component type of the page.
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state,
    DigitalForm|null $digitalForm = NULL,
    Paragraph|null $stepParagraph = NULL,
    Paragraph|null $pageParagraph = NULL,
    CustomSingleQuestionPageType|null $pageComponentType = NULL,
  ) {
    if (empty($digitalForm)) {
      throw new \InvalidArgumentException('Digital Form cannot be null.');
    }
    $this->digitalForm = $digitalForm;

    if (empty($stepParagraph)) {
      throw new \InvalidArgumentException('Step paragraph cannot be null.');
    }
    $this->stepParagraph = $stepParagraph;

    if (empty($pageParagraph) && !$this->allowEmptyPageParagraph) {
      throw new \InvalidArgumentException('Page paragraph cannot be null.');
    }
    $this->pageParagraph = $pageParagraph;
    $this->pageComponentType = $pageComponentType;

    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * Validates the page paragraph.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  abstract protected function validatePageParagraph(array $form, FormStateInterface $form_state);

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->setPageParagraphFromFormState($form_state);
    $this->validatePageParagraph($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the previously validated step paragraph.
    $this->pageParagraph->save();
  }

}
