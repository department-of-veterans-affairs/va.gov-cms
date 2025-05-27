<?php

namespace Drupal\va_gov_form_builder\Form\Base;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Service\DigitalFormsService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Abstract base class for Form Builder step pages.
 */
abstract class FormBuilderStepBase extends FormBuilderBase {

  /**
   * The session key used to store the step title.
   *
   * @var string
   */
  const SESSION_KEY = 'form_builder:add_step:step_label';

  /**
   * The DigitalForm object loaded by this form.
   *
   * @var \Drupal\va_gov_form_builder\EntityWrapper\DigitalForm
   */
  protected $digitalForm;

  /**
   * The paragraph object representing the step edited/created by this form.
   *
   * @var \Drupal\paragraphs\Entity\Paragraph
   */
  protected $stepParagraph;

  /**
   * Flag indicating whether this form allows an empty step paragraph.
   *
   * This defaults to FALSE. The only time an empty step paragraph
   * should be allowed is on the forms associated with initial process
   * of adding a step. This includes all form pages leading up to
   * and including the form page that creates the paragraph for the step.
   * Every other form should operate on an existing paragraph and should
   * require an object to be populated.
   *
   * @var bool
   */
  protected $allowEmptyStepParagraph;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, DigitalFormsService $digitalFormsService, SessionInterface $session) {
    parent::__construct($entityTypeManager, $digitalFormsService, $session);
    $this->allowEmptyStepParagraph = FALSE;
  }

  /**
   * Sets (creates or updates) a step-paragraph object from the form-state data.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  abstract protected function setStepParagraphFromFormState(FormStateInterface $form_state);

  /**
   * Returns a field value from the step paragraph.
   *
   * If a step paragraph is not set, or `fieldName`
   * does not exist, returns NULL. This is primarily
   * used to populate forms with default values when the
   * form edits an existing step paragraph.
   *
   * @param string $fieldName
   *   The name of the field whose value should be fetched.
   */
  protected function getStepParagraphFieldValue($fieldName) {
    if (empty($this->stepParagraph)) {
      return NULL;
    }

    try {
      return $this->stepParagraph->get($fieldName)->value;
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
   * @param \Drupal\va_gov_form_builder\EntityWrapper\DigitalForm $digitalForm
   *   The Digital Form object.
   * @param \Drupal\paragraphs\Entity\Paragraph|null $stepParagraph
   *   The paragraph object representing the step.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $digitalForm = NULL, $stepParagraph = NULL) {
    if (empty($digitalForm)) {
      throw new \InvalidArgumentException('Digital Form cannot be null.');
    }
    $this->digitalForm = $digitalForm;

    if (empty($stepParagraph) && !$this->allowEmptyStepParagraph) {
      throw new \InvalidArgumentException('Step paragraph cannot be null.');
    }
    $this->stepParagraph = $stepParagraph;

    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * Validate the step paragraph.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  abstract protected function validateStepParagraph(array $form, FormStateInterface $form_state);

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->setStepParagraphFromFormState($form_state);
    $this->validateStepParagraph($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the previously validated step paragraph.
    $this->stepParagraph->save();
  }

}
