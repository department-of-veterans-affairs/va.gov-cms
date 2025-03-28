<?php

namespace Drupal\va_gov_form_builder\Form\Base;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\va_gov_form_builder\Service\DigitalFormsService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Abstract base class for Form Builder form pages.
 */
abstract class FormBuilderFormBase extends FormBuilderBase {

  /**
   * The DigitalForm object created or loaded by this form step.
   *
   * @var \Drupal\va_gov_form_builder\EntityWrapper\DigitalForm
   */
  protected $digitalForm;

  /**
   * Flag indicating whether this form allows an empty DigitalForm object.
   *
   * This defaults to FALSE. The only time an empty object
   * should be allowed is on the form that creates
   * the node for the first time. Every other form should
   * operate on an existing form and should require an
   * object to be populated.
   *
   * @var bool
   */
  protected $allowEmptyDigitalForm;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, DigitalFormsService $digitalFormsService, SessionInterface $session) {
    parent::__construct($entityTypeManager, $digitalFormsService, $session);
    $this->allowEmptyDigitalForm = FALSE;
  }

  /**
   * Returns the Digital Form fields accessed by this form step.
   */
  abstract protected function getFields();

  /**
   * Sets (creates or updates) a DigitalForm object from the form-state data.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  abstract protected function setDigitalFormFromFormState(FormStateInterface $form_state);

  /**
   * Returns a field value from the Digital Form.
   *
   * If Digital Form is not set, or `fieldName`
   * does not exist, returns NULL. This is primarily
   * used to populate forms with default values when the
   * form edits an existing Digital Form.
   *
   * @param string $fieldName
   *   The name of the field whose value should be fetched.
   */
  protected function getDigitalFormFieldValue($fieldName) {
    if (empty($this->digitalForm)) {
      return NULL;
    }

    try {
      if ($fieldName === 'title') {
        return $this->digitalForm->getTitle();
      }

      return $this->digitalForm->get($fieldName)->value;
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
   */
  public function buildForm(array $form, FormStateInterface $form_state, $digitalForm = NULL) {
    if (empty($digitalForm) && !$this->allowEmptyDigitalForm) {
      throw new \InvalidArgumentException('Digital Form cannot be null.');
    }
    $this->digitalForm = $digitalForm;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->setDigitalFormFromFormState($form_state);

    // Validate the node entity.
    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
    $violations = $this->digitalForm->validate();

    $this->setFormErrors($form_state, $violations, $this->getFields());
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the previously validated Digital Form.
    $this->digitalForm->save();
  }

}
